<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSolicitudRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SolicitudesController extends Controller
{
    public function store(StoreSolicitudRequest $request)
    {
        $u = $request->user();
        // alumno delegado solo puede para su grupo; admin puede cualquiera
        if ($u->hasRole('alumno') && !$u->hasRole('admin')) {
            // asume pivot grupo_user o campo user.grupo_id; adapta a tu modelo:
            $grupoDelAlumno = DB::table('grupo_integrantes')
                ->where('user_id', $u->id)->value('grupo_id');
            if ((int)$request->grupo_id !== (int)$grupoDelAlumno) {
                abort(403, 'Solo para tu grupo.');
            }
        }

        return DB::transaction(function () use ($request, $u) {
            $id = DB::table('solicitudes')->insertGetId([
                'grupo_id'    => $request->grupo_id,
                'practica_id' => $request->practica_id,
                'estado'      => 'PENDIENTE',
                'comentario'  => $request->comentario,
                'creado_por'  => $u->id,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            $rows = [];
            foreach ($request->items as $it) {
                $rows[] = [
                    'solicitud_id' => $id,
                    'insumo_id'    => $it['insumo_id'],
                    'cantidad'     => $it['cantidad'],
                    'unidad'       => $it['unidad'] ?? null,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ];
            }
            DB::table('solicitud_items')->insert($rows);

            return response()->json(['ok' => true, 'id' => $id], 201);
        });
    }

    public function show(Request $request, int $id)
    {
        // visibilidad básica: admin/tecnico ven todo; alumno solo su grupo
        $u = $request->user();

        $sol = DB::table('solicitudes as s')
            ->leftJoin('grupos as g','g.id','=','s.grupo_id')
            ->leftJoin('practicas as p','p.id','=','s.practica_id')
            ->select('s.*','g.nombre as grupo','p.nombre as practica')
            ->where('s.id',$id)->first();

        if (!$sol) abort(404);

        if ($u->hasRole('alumno') && !$u->hasRole('admin')) {
            $grupoDelAlumno = DB::table('grupo_integrantes')
                ->where('user_id', $u->id)->value('grupo_id');
            if ((int)$sol->grupo_id !== (int)$grupoDelAlumno) {
                abort(403);
            }
        }

        $items = DB::table('solicitud_items as si')
            ->join('insumos as i','i.id','=','si.insumo_id')
            ->select('si.*','i.nombre as insumo')
            ->where('si.solicitud_id',$id)->get();

        return ['solicitud' => $sol, 'items' => $items];
    }

    public function cambiarEstado(Request $request, int $id)
    {
        $u = $request->user();
        if (! $u->hasAnyRole(['admin','tecnico'])) abort(403);

        $data = $request->validate([
            'estado'     => ['required','in:PENDIENTE,APROBADO,RECHAZADO,ENTREGADO'],
            'observacion'=> ['nullable','string','max:500'],
        ]);

        $ok = DB::table('solicitudes')->where('id',$id)->update([
            'estado'      => $data['estado'],
            'observacion' => $data['observacion'] ?? null,
            'atendido_por'=> $u->id,
            'updated_at'  => now(),
        ]);

        if (!$ok) abort(404);
        return ['ok' => true];
    }
}
