<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class PracticaMaterialController extends Controller
{
    private function mustAdminOrProfesor(Request $r){
        $u = $r->user();
        if (!$u || !$u->hasAnyRole(['admin','profesor'])) abort(403);
        return $u->id;
    }

    public function listar(Request $r, int $practicaId){
        // Devuelve filas con nombre del insumo/equipo y categoría si aplica
        $insumos = DB::table('practicas_material_base as pm')
            ->join('insumos as i','i.id','=','pm.item_id')
            ->leftJoin('categorias_insumo as c','c.id','=','i.categoria_id')
            ->where('pm.practica_id',$practicaId)->where('pm.tipo_item','INSUMO')
            ->select([
                'pm.tipo_item','pm.item_id','pm.unidad','pm.cantidad_sugerida',
                'i.codigo','i.nombre','i.unidad as unidad_base','c.nombre as categoria'
            ]);

        $equipos = DB::table('practicas_material_base as pm')
            ->join('equipos as e','e.id','=','pm.item_id')
            ->where('pm.practica_id',$practicaId)->where('pm.tipo_item','EQUIPO')
            ->select([
                'pm.tipo_item','pm.item_id','pm.unidad','pm.cantidad_sugerida',
                'e.codigo','e.nombre'
            ]);

        return $insumos->unionAll($equipos)->get();
    }

    // body: { items: [{tipo_item:'INSUMO'|'EQUIPO', item_id, unidad?, cantidad_sugerida}] }
    public function upsert(Request $r, int $practicaId){
        $this->mustAdminOrProfesor($r);
        $data = $r->validate([
            'items' => ['required','array','min:1'],
            'items.*.tipo_item' => ['required','in:INSUMO,EQUIPO'],
            'items.*.item_id'   => ['required','integer'],
            'items.*.unidad'    => ['nullable','string','max:30'],
            'items.*.cantidad_sugerida' => ['nullable','numeric','min:0'],
        ]);

        DB::transaction(function() use($data,$practicaId){
            foreach($data['items'] as $it){
                DB::table('practicas_material_base')->updateOrInsert(
                    ['practica_id'=>$practicaId,'tipo_item'=>$it['tipo_item'],'item_id'=>$it['item_id']],
                    ['unidad'=>$it['unidad'] ?? null, 'cantidad_sugerida'=>$it['cantidad_sugerida'] ?? null]
                );
            }
        });

        return ['ok'=>true];
    }

    public function eliminar(Request $r, int $practicaId, string $tipo, int $itemId){
        $this->mustAdminOrProfesor($r);
        if(!in_array($tipo, ['INSUMO','EQUIPO'])) abort(422,'tipo inválido');
        $ok = DB::table('practicas_material_base')
            ->where(['practica_id'=>$practicaId,'tipo_item'=>$tipo,'item_id'=>$itemId])
            ->delete();
        if(!$ok) abort(404);
        return ['ok'=>true];
    }
}
