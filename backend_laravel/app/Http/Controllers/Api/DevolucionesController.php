<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class DevolucionesController extends Controller
{
    private function mustTechOrAdmin(Request $r){
        $u = $r->user();
        if (!$u || !$u->hasAnyRole(['admin','tecnico'])) abort(403);
        return $u->id;
    }

    // body: { items:[{equipo_id, estado_equipo:'OK'|'DANADO'|'FALTANTE', observacion?}] }
    public function registrar(Request $r, int $prestamoId){
        $uid = $this->mustTechOrAdmin($r);
        $data = $r->validate([
            'items'=>'required|array|min:1',
            'items.*.equipo_id'=>'required|integer|exists:equipos,id',
            'items.*.estado_equipo'=>'required|in:OK,DANADO,FALTANTE',
            'items.*.observacion'=>'nullable|string|max:255'
        ]);

        DB::transaction(function() use($prestamoId,$data,$uid){
            foreach($data['items'] as $it){
                // insertar devolución (si no existe)
                $exists = DB::table('devoluciones')
                    ->where('prestamo_id',$prestamoId)
                    ->where('equipo_id',$it['equipo_id'])
                    ->exists();
                if(!$exists){
                    DB::table('devoluciones')->insert([
                        'prestamo_id'=>$prestamoId,
                        'equipo_id'=>$it['equipo_id'],
                        'fecha_dev'=>now(),
                        'estado_equipo'=>$it['estado_equipo'],
                        'observacion'=>$it['observacion'] ?? null
                    ]);

                    // kardex equipo (ingreso por devolución)
                    $labId = DB::table('equipos')->where('id',$it['equipo_id'])->value('laboratorio_id') ?? 0;
                    DB::table('kardex_movimientos')->insert([
                        'laboratorio_id'=>$labId,'tipo_item'=>'EQUIPO','item_id'=>$it['equipo_id'],
                        'tipo_mov'=>'INGRESO','cantidad'=>1,'motivo'=>'DEVOLUCION',
                        'referencia'=>'PREST-'.$prestamoId,'fecha'=>now(),'usuario_id'=>$uid
                    ]);
                }
            }

            // actualizar estado del prestamo: si faltan devolver equipos -> PARCIAL, si no -> CERRADO
            $tot  = DB::table('prestamo_items')->where('prestamo_id',$prestamoId)->count();
            $done = DB::table('devoluciones')->where('prestamo_id',$prestamoId)->count();
            $estado = ($tot>0 && $done >= $tot) ? 'CERRADO' : 'PARCIAL';
            DB::table('prestamos')->where('id',$prestamoId)->update(['estado'=>$estado]);
        });

        return ['ok'=>true];
    }
}
