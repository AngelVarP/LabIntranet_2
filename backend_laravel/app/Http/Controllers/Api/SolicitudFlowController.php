<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class SolicitudFlowController extends Controller
{
    private function mustTechOrAdmin(Request $r){
        $u = $r->user();
        if (!$u || !$u->hasAnyRole(['admin','tecnico'])) abort(403);
        return $u->id;
    }
    private function pushHist($solId,$estado,$userId,$coment=null){
        DB::table('solicitud_estados_historial')->insert([
            'solicitud_id'=>$solId,'estado'=>$estado,'usuario_id'=>$userId,
            'comentario'=>$coment,'creado_at'=>now()
        ]);
    }

    public function aprobar(Request $r, int $id){
        $uid = $this->mustTechOrAdmin($r);
        DB::transaction(function() use($id,$uid,$r){
            DB::table('solicitudes')->where('id',$id)->update([
                'estado'=>'APROBADO','actualizado_at'=>now()
            ]);
            $this->pushHist($id,'APROBADO',$uid,$r->input('comentario'));
        });
        return ['ok'=>true];
    }
    public function rechazar(Request $r, int $id){
        $uid = $this->mustTechOrAdmin($r);
        DB::transaction(function() use($id,$uid,$r){
            DB::table('solicitudes')->where('id',$id)->update([
                'estado'=>'RECHAZADO','actualizado_at'=>now()
            ]);
            $this->pushHist($id,'RECHAZADO',$uid,$r->input('comentario'));
        });
        return ['ok'=>true];
    }
    public function preparar(Request $r, int $id){
        $uid = $this->mustTechOrAdmin($r);
        DB::transaction(function() use($id,$uid,$r){
            DB::table('solicitudes')->where('id',$id)->update([
                'estado'=>'PREPARADO','actualizado_at'=>now()
            ]);
            $this->pushHist($id,'PREPARADO',$uid,$r->input('comentario'));
        });
        return ['ok'=>true];
    }

    // body: { items: [{item_id, tipo_item:'INSUMO'|'EQUIPO', cantidad_entregar, laboratorio_id, lote?}], comentario? }
    public function entregar(Request $r, int $id){
        $uid = $this->mustTechOrAdmin($r);
        $data = $r->validate([
            'items'=>'required|array|min:1',
            'items.*.item_id'=>'required|integer',
            'items.*.tipo_item'=>'required|in:INSUMO,EQUIPO',
            'items.*.cantidad_entregar'=>'required|numeric|min:0',
            'items.*.laboratorio_id'=>'required|integer'
        ]);

        DB::transaction(function() use($id,$uid,$data,$r){
            foreach($data['items'] as $it){
                // acumula entregado
                DB::table('solicitud_items')
                    ->where('solicitud_id',$id)
                    ->where('item_id',$it['item_id'])
                    ->where('tipo_item',$it['tipo_item'])
                    ->update([
                        'cantidad_entregada'=>DB::raw('COALESCE(cantidad_entregada,0)+'.(float)$it['cantidad_entregar'])
                    ]);

                // kardex (egreso)
                DB::table('kardex_movimientos')->insert([
                    'laboratorio_id'=>$it['laboratorio_id'],
                    'tipo_item'=>$it['tipo_item'],
                    'item_id'=>$it['item_id'],
                    'tipo_mov'=>'EGRESO',
                    'cantidad'=>$it['cantidad_entregar'],
                    'motivo'=>'ENTREGA_SOLICITUD',
                    'referencia'=>'SOL-'.$id,
                    'fecha'=>now(),
                    'usuario_id'=>$uid
                ]);

                // opcional: descontar de lotes (el que tú definas)
                // DB::table('insumo_lotes')->where(...)->decrement('cantidad', $it['cantidad_entregar']);
            }

            // si todos los items llegaron a la meta, mueve a ENTREGADO
            $row = DB::table('solicitud_items')
                    ->selectRaw('SUM(COALESCE(cantidad_solic,0)) as s, SUM(COALESCE(cantidad_entregada,0)) as e')
                    ->where('solicitud_id',$id)->first();
            if ($row && $row->s>0 && $row->e >= $row->s) {
                DB::table('solicitudes')->where('id',$id)->update(['estado'=>'ENTREGADO','actualizado_at'=>now()]);
                $this->pushHist($id,'ENTREGADO',$uid,$r->input('comentario'));
            } else {
                // mantener PREPARADO o estado intermedio; registramos transición informativa
                $this->pushHist($id,'ENTREGA_PARCIAL',$uid,$r->input('comentario'));
            }
        });
        return ['ok'=>true];
    }

    public function cerrar(Request $r, int $id){
        $uid = $this->mustTechOrAdmin($r);
        DB::transaction(function() use($id,$uid,$r){
            DB::table('solicitudes')->where('id',$id)->update([
                'estado'=>'CERRADO','actualizado_at'=>now()
            ]);
            $this->pushHist($id,'CERRADO',$uid,$r->input('comentario'));
        });
        return ['ok'=>true];
    }
}
