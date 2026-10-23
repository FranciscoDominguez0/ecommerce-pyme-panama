<?php

namespace App\Services;

use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Usuario;
use App\Models\ValoracionProducto;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ValoracionService
{
    /**
     * Verifica si un cliente está habilitado para valorar un producto específico de un pedido.
     * Requisitos estrictos de compra verificada:
     * 1. El pedido pertenece al usuario autenticado.
     * 2. El último estado del pedido es exactamente 'entregado'.
     * 3. El producto forma parte de los artículos adquiridos en dicho pedido.
     */
    public function puedeValorar(Usuario $usuario, Pedido $pedido, int $productoId): bool
    {
        // 1. Validar propiedad del pedido
        if ((int) $pedido->usuario_id !== (int) $usuario->id) {
            return false;
        }

        // 2. Validar que el pedido esté en estado 'entregado'
        $ultimoEstado = $pedido->ultimoEstado?->estado;
        if ($ultimoEstado !== 'entregado') {
            return false;
        }

        // 3. Validar que el producto esté entre los items del pedido
        $perteneceAlPedido = $pedido->items()
            ->where('producto_id', $productoId)
            ->exists();

        return $perteneceAlPedido;
    }

    /**
     * Sanitiza cadenas de texto para prevenir inyecciones maliciosas (XSS, scripts, HTML tags, null bytes).
     */
    public function sanitizarTexto(?string $texto, int $longitudMaxima = 1000): ?string
    {
        if ($texto === null) {
            return null;
        }

        // 1. Eliminar bloques <script>...</script>, <style>...</style>, <iframe>...</iframe> junto con su contenido
        $limpio = preg_replace('/<(script|style|iframe)[^>]*?>.*?<\/\1>/si', '', $texto) ?? '';

        // 2. Eliminar cualquier otra etiqueta HTML o PHP restante
        $limpio = strip_tags($limpio);

        // 3. Eliminar caracteres de control invisibles y null-bytes maliciosos
        $limpio = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $limpio) ?? '';

        // 4. Normalizar espacios en blanco y saltos de línea repetidos
        $limpio = trim($limpio);

        if ($limpio === '') {
            return null;
        }

        // 5. Truncar a la longitud máxima segura con soporte UTF-8
        return mb_substr($limpio, 0, $longitudMaxima, 'UTF-8');
    }

    /**
     * Registra o actualiza una valoración de producto para un pedido entregado.
     *
     * @throws ValidationException
     */
    public function guardarValoracion(
        Usuario $usuario,
        Pedido $pedido,
        int $productoId,
        int $calificacion,
        ?string $titulo = null,
        ?string $comentario = null
    ): ValoracionProducto {
        // Asegurar la relación del último estado del pedido
        if (!$pedido->relationLoaded('ultimoEstado')) {
            $pedido->load('ultimoEstado');
        }

        // Validación defensiva de elegibilidad
        if (!$this->puedeValorar($usuario, $pedido, $productoId)) {
            throw ValidationException::withMessages([
                'producto_id' => 'Solo puedes valorar productos de compras verificadas que hayan sido entregadas exitosamente.',
            ]);
        }

        // Validar rango numérico de calificación
        $calificacionAjustada = max(1, min(5, $calificacion));

        // Sanitización estricta contra código malicioso
        $tituloSanitizado = $this->sanitizarTexto($titulo, 120);
        $comentarioSanitizado = $this->sanitizarTexto($comentario, 1000);

        return DB::transaction(function () use (
            $usuario,
            $pedido,
            $productoId,
            $calificacionAjustada,
            $tituloSanitizado,
            $comentarioSanitizado
        ) {
            return ValoracionProducto::updateOrCreate(
                [
                    'pedido_id' => $pedido->id,
                    'producto_id' => $productoId,
                ],
                [
                    'usuario_id' => $usuario->id,
                    'calificacion' => $calificacionAjustada,
                    'titulo' => $tituloSanitizado,
                    'comentario' => $comentarioSanitizado,
                    'es_aprobado' => true,
                ]
            );
        });
    }

    /**
     * Calcula métricas y distribución de estrellas para un producto.
     */
    public function obtenerEstadisticasProducto(int $productoId): array
    {
        $conteoPorEstrella = ValoracionProducto::where('producto_id', $productoId)
            ->where('es_aprobado', true)
            ->selectRaw('calificacion, COUNT(*) as total')
            ->groupBy('calificacion')
            ->pluck('total', 'calificacion')
            ->all();

        $total = array_sum($conteoPorEstrella);
        $sumaPuntajes = 0;
        $distribucion = [
            5 => ['cantidad' => 0, 'porcentaje' => 0],
            4 => ['cantidad' => 0, 'porcentaje' => 0],
            3 => ['cantidad' => 0, 'porcentaje' => 0],
            2 => ['cantidad' => 0, 'porcentaje' => 0],
            1 => ['cantidad' => 0, 'porcentaje' => 0],
        ];

        foreach ($distribucion as $estrella => &$datos) {
            $cantidad = (int) ($conteoPorEstrella[$estrella] ?? 0);
            $datos['cantidad'] = $cantidad;
            $datos['porcentaje'] = $total > 0 ? round(($cantidad / $total) * 100) : 0;
            $sumaPuntajes += ($estrella * $cantidad);
        }
        unset($datos);

        $promedio = $total > 0 ? round($sumaPuntajes / $total, 1) : 0.0;

        return [
            'total' => $total,
            'promedio' => $promedio,
            'distribucion' => $distribucion,
        ];
    }
}
