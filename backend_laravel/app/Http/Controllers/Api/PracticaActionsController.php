<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class PracticaActionsController extends Controller
{
    // body: { grupo_id, delegado_id?, estado_inicial? ('BORRADOR'|'PENDIENTE'), prioridad? }
    public function crearSolicitud(Request $r, int $practicaId){
        $u = $r->user(); if(!$u) abort(401);

        $data = $r->validate([
            'grupo_id'       => ['required','integer','exists:grupos,id'],
            'delegado_id'    => ['nullable','integer','exists:users,id'],
            'estado_inicial' => ['nullable','in:BORRADOR,PENDIENTE'],
            'prioridad'      => ['nullable','in:ALTA,MEDIA,BAJA']
        ]);

        // Carga práctica
        $pr = DB::table('practicas')->where('id',$practicaId)->first();
        if(!$pr) abort(404, 'Práctica no encontrada');

        $estado = $data['estado_inicial'] ?? 'BORRADOR';
        $prior  = $data['prioridad'] ?? null;

        $solId = null;

        DB::transaction(function() use($pr,$data,$u,&$solId,$practicaId){
            // Crear solicitud
            $solId = DB::table('solicitudes')->insertGetId([
                'practica_id'   => $pr->id,
                'laboratorio_id'=> $pr->laboratorio_id,
                'grupo_id'      => $data['grupo_id'],
                'delegado_id'   => $data['delegado_id'] ?? (DB::table('grupos')->where('id',$data['grupo_id'])->value('delegado_usuario_id')),
                'estado'        => $data['estado_inicial'] ?? 'BORRADOR',
                'prioridad'     => $prior ?? null,
                'observaciones' => 'Creada desde práctica',
                'creado_por'    => $u->id,
                'creado_at'     => now(),
                'actualizado_at'=> now(),
            ]);

            // Copiar material base a solicitud_items
            $base = DB::table('practicas_material_base')->where('practica_id',$practicaId)->get();
            foreach($base as $b){
                DB::table('solicitud_items')->updateOrInsert(
                    ['solicitud_id'=>$solId,'tipo_item'=>$b->tipo_item,'item_id'=>$b->item_id],
                    [
                        'unidad' => $b->unidad,
                        'cantidad_solic' => $b->cantidad_sugerida,
                        'observacion' => 'Auto desde práctica'
                    ]
                );
            }

            // Historial inicial
            DB::table('solicitud_estados_historial')->insert([
                'solicitud_id'=>$solId,
                'estado'=>$data['estado_inicial'] ?? 'BORRADOR',
                'usuario_id'=>$u->id,
                'comentario'=>'Creada desde práctica',
                'creado_at'=>now()
            ]);
        });

        return response()->json(['ok'=>true,'solicitud_id'=>$solId], 201);
    }
}
