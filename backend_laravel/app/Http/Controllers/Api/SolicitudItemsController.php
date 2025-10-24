<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class SolicitudItemsController extends Controller
{
    // Requiere rol admin|tecnico|profesor (ajusta si quieres más fino)
    private function mustEditor(Request $r){
        $u=$r->user(); if(!$u || !$u->hasAnyRole(['admin','tecnico','profesor'])) abort(403);
        return $u;
    }

    /**
     * Body:
     * {
     *   "tipo_item": "INSUMO"|"EQUIPO",
     *   "item_id": 123,
     *   "unidad": "u" (opcional),
     *   "cantidad_solic": 2.5 (opcional, default 1)
     * }
     */
    public function agregar(Request $r, int $id){
        $this->mustEditor($r);
        $data = $r->validate([
            'tipo_item'       => ['required','in:INSUMO,EQUIPO'],
            'item_id'         => ['required','integer'],
            'unidad'          => ['nullable','string','max:30'],
            'cantidad_solic'  => ['nullable','numeric','min:0.0001'],
        ]);
        $cant = $data['cantidad_solic'] ?? 1;

        // si ya existe, suma; si no, crea
        $exists = DB::table('solicitud_items')->where([
            'solicitud_id'=>$id,
            'tipo_item'=>$data['tipo_item'],
            'item_id'=>$data['item_id'],
        ])->first();

        if($exists){
            DB::table('solicitud_items')->where('id',$exists->id)->update([
                'cantidad_solic' => DB::raw('COALESCE(cantidad_solic,0) + '.(float)$cant),
                'unidad' => $data['unidad'] ?? $exists->unidad,
            ]);
        }else{
            DB::table('solicitud_items')->insert([
                'solicitud_id'=>$id,
                'tipo_item'=>$data['tipo_item'],
                'item_id'=>$data['item_id'],
                'unidad'=>$data['unidad'] ?? null,
                'cantidad_solic'=>$cant,
                'cantidad_entregada'=>0,
            ]);
        }
        return ['ok'=>true];
    }

    /**
     * Body:
     * {
     *   "tipo_item": "INSUMO"|"EQUIPO",
     *   "item_id": 123
     * }
     */
    public function quitar(Request $r, int $id){
        $this->mustEditor($r);
        $data = $r->validate([
            'tipo_item' => ['required','in:INSUMO,EQUIPO'],
            'item_id'   => ['required','integer'],
        ]);

        $ok = DB::table('solicitud_items')->where([
            'solicitud_id'=>$id,
            'tipo_item'=>$data['tipo_item'],
            'item_id'=>$data['item_id'],
        ])->delete();

        if(!$ok) abort(404,'Ítem no encontrado en la solicitud');
        return ['ok'=>true];
    }
}
