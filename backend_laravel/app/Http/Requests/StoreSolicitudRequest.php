<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSolicitudRequest extends FormRequest
{
    public function authorize(): bool 
    {
        $u = $this->user();
        return $u && ($u->hasRole('admin') || ($u->hasRole('alumno') && (bool)$u->delegado));
    } // acceso lo valida la Policy

    public function rules(): array
    {
        return [
            'practica_id'    => 'required|exists:practicas,id',
            'laboratorio_id' => 'required|exists:laboratorios,id',
            'items'          => 'required|array|min:1',
            'items.*.tipo_item' => 'required|in:INSUMO,EQUIPO',
            'items.*.item_id'   => 'required|integer',
            'items.*.cantidad_solic' => 'nullable|numeric|min:0',
            'items.*.unidad'    => 'nullable|string|max:30',
            'prioridad'      => 'nullable|in:ALTA,MEDIA,BAJA',
            'observaciones'  => 'nullable|string|max:1000',
        ];
    }
}
