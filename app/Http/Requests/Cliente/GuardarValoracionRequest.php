<?php

namespace App\Http\Requests\Cliente;

use Illuminate\Foundation\Http\FormRequest;

class GuardarValoracionRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para realizar esta solicitud.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Reglas de validación para registrar o actualizar una valoración de producto.
     */
    public function rules(): array
    {
        return [
            'producto_id' => ['required', 'integer', 'exists:productos,id'],
            'calificacion' => ['required', 'integer', 'between:1,5'],
            'titulo' => ['nullable', 'string', 'max:120'],
            'comentario' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Mensajes de error personalizados en español.
     */
    public function messages(): array
    {
        return [
            'producto_id.required' => 'El producto a valorar es obligatorio.',
            'producto_id.exists' => 'El producto seleccionado no es válido.',
            'calificacion.required' => 'Por favor selecciona una calificación con estrellas.',
            'calificacion.between' => 'La calificación debe ser de 1 a 5 estrellas.',
            'titulo.max' => 'El título de la opinión no puede exceder los 120 caracteres.',
            'comentario.max' => 'El comentario no puede superar los 1000 caracteres.',
        ];
    }
}
