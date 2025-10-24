<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SolicitudesFlowController extends Controller
{
    // ---- helpers de autorización ----
    private function mustProfesorOrAdmin(Request $r){
        $u=$r->user(); if(!$u || !$u->hasAnyRole(['profesor','admin'])) abort(403,'No autorizado'); return $u->id;
    }
    private function mustTecOrAdmin(Request $r){
        $u=$r->user(); if(!$u || !$u->hasAnyRole(['tecnico','admin'])) abort(403,'No autorizado'); return $u->id;
    }

    // ---- util: obtiene solicitud con estado actual ----
    private function getSolicitud(int $id){
        $s = DB::table('solicitudes')->where('id',$id)->first([
            'id','estado','prioridad','laboratorio_id','grupo_id','practica_id','delegado_id','creado_por','actualizado_at'
        ]);
        if(!$s) abort(404,'Solicitud no encontrada');
        return $s;
    }

    // ---- util: guarda historial ----
    private function pushHistorial(int $solicitudId, string $estado, int $userId, ?string $comentario=null){
        DB::table('solicitud_estados_historial')->insert([
            'solicitud_id' => $solicitudId,
            'estado'       => $estado,
            'usuario_id'   => $userId,
            'comentario'   => $comentario,
            'creado_at'    => now(),
        ]);
    }

    // ---- util: cambio de estado atómico con historial ----
     private function setEstadoConHistorial(int $id, string $nuevo, int $userId, ?string $comentario=null){
        // update estado
        DB::table('solicitudes')->where('id',$id)->update([
            'estado'         => $nuevo,
            'actualizado_at' => now(),
        ]);

        // historial
        DB::table('solicitud_estados_historial')->insert([
            'solicitud_id' => $id,
            'estado'       => $nuevo,
            'usuario_id'   => $userId,
            'comentario'   => $comentario,
            'creado_at'    => now(),
        ]);

        // notificaciones (delegado y creador, si existen)
        $s = DB::table('solicitudes')->where('id',$id)->first(['delegado_id','creado_por','grupo_id','laboratorio_id']);
        if($s){
            $titulo = "Solicitud #{$id} → {$nuevo}";
            $cuerpo = $comentario ? $comentario : 'El estado de la solicitud ha cambiado.';
            $rows   = [];

            // notifica a delegado
            if(!empty($s->delegado_id)){
                $rows[] = [
                    'usuario_id' => (int)$s->delegado_id,
                    'tipo'       => 'SOLICITUD_ESTADO',
                    'titulo'     => $titulo,
                    'cuerpo'     => $cuerpo,
                    'leida'      => 0,
                    'creado_at'  => now(),
                    'ref_entidad'=> 'SOLICITUD',
                    'ref_id'     => $id,
                ];
            }
            // notifica a quien creó
            if(!empty($s->creado_por) && (int)$s->creado_por !== (int)($s->delegado_id ?? 0)){
                $rows[] = [
                    'usuario_id' => (int)$s->creado_por,
                    'tipo'       => 'SOLICITUD_ESTADO',
                    'titulo'     => $titulo,
                    'cuerpo'     => $cuerpo,
                    'leida'      => 0,
                    'creado_at'  => now(),
                    'ref_entidad'=> 'SOLICITUD',
                    'ref_id'     => $id,
                ];
            }

            if(!empty($rows)){
                DB::table('notificaciones')->insert($rows);
            }
        }
    }

    /**
     * POST /api/solicitudes/{id}/aprobar
     * body: { comentario?: string }
     * Permite: profesor/admin
     * De: PENDIENTE -> APROBADO
     */
    public function aprobar(Request $r, int $id){
        $uid = $this->mustProfesorOrAdmin($r);
        $data = $r->validate(['comentario'=>['nullable','string','max:255']]);

        DB::transaction(function() use($id,$uid,$data){
            $s = $this->getSolicitud($id);
            if($s->estado !== 'PENDIENTE') abort(422,'Solo se puede aprobar si está PENDIENTE');
            $this->setEstadoConHistorial($id,'APROBADO',$uid,$data['comentario'] ?? null);
        });

        return ['ok'=>true,'estado'=>'APROBADO'];
    }

    /**
     * POST /api/solicitudes/{id}/rechazar
     * body: { comentario?: string }
     * Permite: profesor/admin
     * De: PENDIENTE -> RECHAZADO
     */
    public function rechazar(Request $r, int $id){
        $uid = $this->mustProfesorOrAdmin($r);
        $data = $r->validate(['comentario'=>['nullable','string','max:255']]);

        DB::transaction(function() use($id,$uid,$data){
            $s = $this->getSolicitud($id);
            if($s->estado !== 'PENDIENTE') abort(422,'Solo se puede rechazar si está PENDIENTE');
            $this->setEstadoConHistorial($id,'RECHAZADO',$uid,$data['comentario'] ?? null);
        });

        return ['ok'=>true,'estado'=>'RECHAZADO'];
    }

    /**
     * POST /api/solicitudes/{id}/preparar
     * body: { comentario?: string }
     * Permite: técnico/admin
     * De: APROBADO -> PREPARADO
     */
    public function preparar(Request $r, int $id){
        $uid = $this->mustTecOrAdmin($r);
        $data = $r->validate(['comentario'=>['nullable','string','max:255']]);

        DB::transaction(function() use($id,$uid,$data){
            $s = $this->getSolicitud($id);
            if($s->estado !== 'APROBADO') abort(422,'Solo se puede preparar si está APROBADO');
            $this->setEstadoConHistorial($id,'PREPARADO',$uid,$data['comentario'] ?? null);
        });

        return ['ok'=>true,'estado'=>'PREPARADO'];
    }

    /**
     * POST /api/solicitudes/{id}/entregar
     * Permite: técnico/admin
     * De: PREPARADO -> ENTREGADO
     * Body opcional (para parciales o sobreescribir cantidades):
     * {
     *   comentario?: string,
     *   items?: [
     *     { id: solicitud_item_id, cantidad_entregada: number, observacion?: string }
     *   ]
     * }
     * Si no se envía "items", por defecto entrega todo lo solicitado (full).
     */
    public function entregar(Request $r, int $id){
        $uid = $this->mustTecOrAdmin($r);
        $data = $r->validate([
            'comentario' => ['nullable','string','max:255'],
            'items'      => ['nullable','array'],
            'items.*.id' => ['required_with:items','integer'],
            'items.*.cantidad_entregada' => ['required_with:items','numeric','min:0'],
            'items.*.observacion' => ['nullable','string','max:255'],
        ]);

        DB::transaction(function() use($id,$uid,$data){
            $s = $this->getSolicitud($id);
            if($s->estado !== 'PREPARADO') abort(422,'Solo se puede entregar si está PREPARADO');

            if(isset($data['items']) && is_array($data['items'])){
                // Parcial / específica
                foreach($data['items'] as $it){
                    DB::table('solicitud_items')
                        ->where('id',$it['id'])
                        ->where('solicitud_id',$id)
                        ->update([
                            'cantidad_entregada' => $it['cantidad_entregada'],
                            'observacion'        => $it['observacion'] ?? DB::raw('observacion'),
                        ]);
                }
            } else {
                // Full delivery: cantidad_entregada = cantidad_solic
                DB::table('solicitud_items')
                    ->where('solicitud_id',$id)
                    ->update([
                        'cantidad_entregada' => DB::raw('COALESCE(cantidad_solic,0)')
                    ]);
            }

            $this->setEstadoConHistorial($id,'ENTREGADO',$uid,$data['comentario'] ?? null);
        });

        return ['ok'=>true,'estado'=>'ENTREGADO'];
    }

    /**
     * POST /api/solicitudes/{id}/cerrar
     * Permite: técnico/admin
     * De: ENTREGADO -> CERRADO
     * body: { comentario?: string }
     */
    public function cerrar(Request $r, int $id){
        $uid = $this->mustTecOrAdmin($r);
        $data = $r->validate(['comentario'=>['nullable','string','max:255']]);

        DB::transaction(function() use($id,$uid,$data){
            $s = $this->getSolicitud($id);
            if($s->estado !== 'ENTREGADO') abort(422,'Solo se puede cerrar si está ENTREGADO');
            $this->setEstadoConHistorial($id,'CERRADO',$uid,$data['comentario'] ?? null);
        });

        return ['ok'=>true,'estado'=>'CERRADO'];
    }
}
