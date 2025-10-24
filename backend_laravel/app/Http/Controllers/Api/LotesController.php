<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class LotesController extends Controller
{
    private function mustTechOrAdmin(Request $r){
        $u = $r->user();
        if (!$u || !$u->hasAnyRole(['admin','tecnico'])) abort(403);
        return $u->id;
    }

    public function listar(Request $r, int $insumo){
        return DB::table('insumo_lotes as il')
            ->join('laboratorios as l','l.id','=','il.laboratorio_id')
            ->where('il.insumo_id',$insumo)
            ->select('il.id','il.lote','il.caducidad','il.cantidad','l.id as laboratorio_id','l.nombre as laboratorio')
            ->orderBy('il.caducidad')
            ->get();
    }

    // body: { laboratorio_id, lote?, caducidad?, cantidad }
    public function crear(Request $r, int $insumo){
        $uid = $this->mustTechOrAdmin($r);
        $data = $r->validate([
            'laboratorio_id'=>'required|integer|exists:laboratorios,id',
            'lote'=>'nullable|string|max:80',
            'caducidad'=>'nullable|date',
            'cantidad'=>'required|numeric'
        ]);

        $id = DB::table('insumo_lotes')->insertGetId([
            'insumo_id'=>$insumo,
            'laboratorio_id'=>$data['laboratorio_id'],
            'lote'=>$data['lote'] ?? null,
            'caducidad'=>$data['caducidad'] ?? null,
            'cantidad'=>$data['cantidad'],
        ]);

        // kardex
        DB::table('kardex_movimientos')->insert([
            'laboratorio_id'=>$data['laboratorio_id'],
            'tipo_item'=>'INSUMO','item_id'=>$insumo,'tipo_mov'=> ($data['cantidad']>=0?'INGRESO':'EGRESO'),
            'cantidad'=>abs($data['cantidad']),'motivo'=>'AJUSTE_LOTE',
            'referencia'=>'LOTE-'.$id,'fecha'=>now(),'usuario_id'=>$uid
        ]);

        return response()->json(['ok'=>true,'id'=>$id],201);
    }

    // body: { delta, destino_laboratorio_id? }  (delta positivo = suma; negativo = resta)
    public function ajustar(Request $r, int $insumo, int $loteId){
        $uid = $this->mustTechOrAdmin($r);
        $data = $r->validate([
            'delta'=>'required|numeric',
            'destino_laboratorio_id'=>'nullable|integer|exists:laboratorios,id'
        ]);

        $lote = DB::table('insumo_lotes')->where('id',$loteId)->where('insumo_id',$insumo)->first();
        if(!$lote) abort(404);

        DB::transaction(function() use($data,$lote,$insumo,$loteId,$uid){
            // 1) ajustar lote actual
            DB::table('insumo_lotes')->where('id',$loteId)->update([
                'cantidad'=>DB::raw('cantidad + ('.(float)$data['delta'].')')
            ]);

            DB::table('kardex_movimientos')->insert([
                'laboratorio_id'=>$lote->laboratorio_id,
                'tipo_item'=>'INSUMO','item_id'=>$insumo,
                'tipo_mov'=> ($data['delta']>=0?'INGRESO':'EGRESO'),
                'cantidad'=>abs($data['delta']),
                'motivo'=>'AJUSTE_LOTE','referencia'=>'LOTE-'.$loteId,
                'fecha'=>now(),'usuario_id'=>$uid
            ]);

            // 2) si hay destino, crear lote espejo (transferencia)
            if (!empty($data['destino_laboratorio_id'])) {
                DB::table('insumo_lotes')->insert([
                    'insumo_id'=>$insumo,
                    'laboratorio_id'=>$data['destino_laboratorio_id'],
                    'lote'=>$lote->lote,
                    'caducidad'=>$lote->caducidad,
                    'cantidad'=>abs($data['delta']),
                ]);
                DB::table('kardex_movimientos')->insert([
                    'laboratorio_id'=>$data['destino_laboratorio_id'],
                    'tipo_item'=>'INSUMO','item_id'=>$insumo,
                    'tipo_mov'=>'INGRESO','cantidad'=>abs($data['delta']),
                    'motivo'=>'TRANSFERENCIA','referencia'=>'LOTE-'.$loteId,
                    'fecha'=>now(),'usuario_id'=>$uid
                ]);
            }
        });

        return ['ok'=>true];
    }
}
