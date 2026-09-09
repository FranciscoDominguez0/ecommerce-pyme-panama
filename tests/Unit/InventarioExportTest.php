<?php

namespace Tests\Unit;

use App\Exports\InventarioExport;
use App\Exports\StockActualExport;
use App\Models\Categoria;
use App\Models\Producto;
use Tests\TestCase;

class InventarioExportTest extends TestCase
{
    /**
     * Verifica que InventarioExport calcule métricas y mapee estados de inventario correctamente.
     */
    public function test_inventario_export_calcula_metricas_y_estados(): void
    {
        $categoria = new Categoria(['nombre' => 'Tecnología']);

        $prod1 = new Producto([
            'sku' => 'LAP-001',
            'nombre' => 'Laptop Gamer',
            'precio' => 1000.00,
            'stock' => 10,
            'stock_minimo' => 5,
        ]);
        $prod1->setRelation('categoria', $categoria);

        $prod2 = new Producto([
            'sku' => 'MOU-002',
            'nombre' => 'Mouse Óptico',
            'precio' => 25.00,
            'stock' => 2,
            'stock_minimo' => 5,
        ]);
        $prod2->setRelation('categoria', $categoria);

        $prod3 = new Producto([
            'sku' => 'TECL-003',
            'nombre' => 'Teclado Mecánico',
            'precio' => 80.00,
            'stock' => 0,
            'stock_minimo' => 3,
        ]);
        $prod3->setRelation('categoria', $categoria);

        $productos = collect([$prod1, $prod2, $prod3]);
        $export = new InventarioExport($productos);

        // Comprobamos la colección
        $this->assertCount(3, $export->collection());

        // Comprobamos los encabezados
        $headings = $export->headings();
        $this->assertContains('SKU', $headings);
        $this->assertContains('PRODUCTO', $headings);
        $this->assertContains('VALOR TOTAL (USD)', $headings);

        // Mapeo producto 1: stock > minimo => En Stock
        $fila1 = $export->map($prod1);
        $this->assertEquals('LAP-001', $fila1[0]);
        $this->assertEquals('Laptop Gamer', $fila1[1]);
        $this->assertEquals('Tecnología', $fila1[2]);
        $this->assertEquals('En Stock', $fila1[3]);
        $this->assertEquals(10, $fila1[4]);
        $this->assertEquals(1000.00, $fila1[5]);
        $this->assertEquals(10000.00, $fila1[6]);

        // Mapeo producto 2: 0 < stock <= minimo => Bajo Stock
        $fila2 = $export->map($prod2);
        $this->assertEquals('Bajo Stock', $fila2[3]);
        $this->assertEquals(50.00, $fila2[6]);

        // Mapeo producto 3: stock = 0 => Agotado
        $fila3 = $export->map($prod3);
        $this->assertEquals('Agotado', $fila3[3]);
        $this->assertEquals(0.00, $fila3[6]);
    }

    /**
     * Verifica que StockActualExport mapee ítems y calcule valorizaciones correctamente.
     */
    public function test_stock_actual_export_mapea_datos_y_estados(): void
    {
        $categoria = new Categoria(['nombre' => 'Ropa']);

        $item1 = (object) [
            'sku' => 'CAM-ROJ-S',
            'nombre_completo' => 'Camiseta Algodón (Rojo / S)',
            'categoria' => $categoria,
            'stock' => 8,
            'stock_minimo' => 3,
            'precio' => 15.00,
        ];

        $item2 = (object) [
            'sku' => 'CAM-AZU-L',
            'nombre_completo' => 'Camiseta Algodón (Azul / L)',
            'categoria' => $categoria,
            'stock' => 0,
            'stock_minimo' => 5,
            'precio' => 15.00,
        ];

        $export = new StockActualExport(collect([$item1, $item2]));

        $this->assertCount(2, $export->collection());

        $fila1 = $export->map($item1);
        $this->assertEquals('CAM-ROJ-S', $fila1[0]);
        $this->assertEquals('Camiseta Algodón (Rojo / S)', $fila1[1]);
        $this->assertEquals('En Stock', $fila1[3]);
        $this->assertEquals(120.00, $fila1[6]);

        $fila2 = $export->map($item2);
        $this->assertEquals('Agotado', $fila2[3]);
        $this->assertEquals(0.00, $fila2[6]);
    }
}
