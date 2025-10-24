<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class PrestamosController extends Controller
{
    private function mustTechOrAdmin(Request $r){
        $u = $r->user();
        if (!$u || !$u->hasAnyRole(['admin','tecnico'])) abort(403);
        return $u->id;
    }

    // body: { solicitud_id, responsable_id, fecha_compromiso? }
    public function store(Request $r){
        $uid = $this->mustTechOrAdmin($r);
        $data = $r->validate([
            'solicitud_id'=>'required|integer|exists:solicitudes,id',
            'responsable_id'=>'required|integer|exists:users,id',
            'fecha_compromiso'=>'nullable|date'
        ]);

        $id = DB::table('prestamos')->insertGetId([
            'solicitud_id'=>$data['solicitud_id'],
            'responsable_id'=>$data['responsable_id'],
            'fecha_compromiso'=>$data['fecha_compromiso'] ?? null,
            'estado'=>'ABIERTO',
            'fecha_prestamo'=>now(),
        ]);

        // opcional: registrar auditoría
        // Audit::log($uid,'CREAR_PRESTAMO','prestamos',$id,$data);

        return response()->json(['ok'=>true,'id'=>$id],201);
    }

    // body: { equipos: [equipo_id, ...] }
    public function agregarEquipo(Request $r, int $id){
        $uid = $this->mustTechOrAdmin($r);
        $data = $r->validate(['equipos'=>'required|array|min:1','equipos.*'=>'integer|exists:equipos,id']);

        DB::transaction(function() use($id,$data,$uid){
            foreach($data['equipos'] as $eqId){
                DB::table('prestamo_items')->updateOrInsert(
                    ['prestamo_id'=>$id,'equipo_id'=>$eqId],
                    ['observacion'=>null]
                );
                // kardex equipo (salida a préstamo)
                DB::table('kardex_movimientos')->insert([
                    'laboratorio_id'=>DB::table('equipos')->where('id',$eqId)->value('laboratorio_id') ?? 0,
                    'tipo_item'=>'EQUIPO','item_id'=>$eqId,'tipo_mov'=>'EGRESO',
                    'cantidad'=>1,'motivo'=>'PRESTAMO','referencia'=>'PREST-'.$id,'fecha'=>now(),'usuario_id'=>$uid
                ]);
            }
            // Audit::log($uid,'AGREGAR_EQUIPO_PRESTAMO','prestamos',$id,$data);
        });

        return ['ok'=>true];
    }

    public function show(Request $r, int $id){
        $cab = DB::table('prestamos as p')
            ->join('users as u','u.id','=','p.responsable_id')
            ->select('p.*','u.name as responsable')->where('p.id',$id)->first();
        if(!$cab) abort(404);

        $items = DB::table('prestamo_items as pi')
            ->join('equipos as e','e.id','=','pi.equipo_id')
            ->leftJoin('devoluciones as d', function($j){ $j->on('d.prestamo_id','=','pi.prestamo_id')->on('d.equipo_id','=','pi.equipo_id'); })
            ->select('pi.equipo_id','e.nombre','e.codigo','d.id as devolucion_id','d.fecha_dev','d.estado_equipo','d.observacion')
            ->where('pi.prestamo_id',$id)->get();

        return ['cabecera'=>$cab,'items'=>$items];
    }

    public function cerrar(Request $r, int $id){
        $uid = $this->mustTechOrAdmin($r);
        DB::transaction(function() use($id,$uid){
            DB::table('prestamos')->where('id',$id)->update(['estado'=>'CERRADO']);
            // Audit::log($uid,'CERRAR_PRESTAMO','prestamos',$id,null);
        });
        return ['ok'=>true];
    }
}
