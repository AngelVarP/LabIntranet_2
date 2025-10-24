<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSolicitudRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema; 

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
        $u = $request->user();

        // Base: solicitudes + grupo + laboratorio (si existe)
        $q = DB::table('solicitudes as s')
            ->leftJoin('grupos as g','g.id','=','s.grupo_id');

        if (Schema::hasColumn('solicitudes','laboratorio_id')) {
            $q->leftJoin('laboratorios as l','l.id','=','s.laboratorio_id');
        }

        $select = ['s.*', 'g.nombre as grupo'];
        if (Schema::hasColumn('solicitudes','laboratorio_id')) {
            $select[] = 'l.nombre as laboratorio';
        }

        // Título de la práctica, priorizando la vista si existe
        if (Schema::hasTable('vw_tablon_laboratorio') && Schema::hasColumn('vw_tablon_laboratorio','practica_titulo')) {
            $q->leftJoin('vw_tablon_laboratorio as v','v.solicitud_id','=','s.id');
            $select[] = 'v.practica_titulo as practica';
        } elseif (Schema::hasTable('practicas') && Schema::hasColumn('solicitudes','practica_id')) {
            $q->leftJoin('practicas as p','p.id','=','s.practica_id');
            // usa la columna que exista: nombre o titulo
            if (Schema::hasColumn('practicas','nombre')) {
                $select[] = 'p.nombre as practica';
            } elseif (Schema::hasColumn('practicas','titulo')) {
                $select[] = 'p.titulo as practica';
            }
        }

        $sol = $q->select($select)->where('s.id',$id)->first();
        if (!$sol) abort(404, 'Solicitud no encontrada');

        // visibilidad alumno: solo su grupo
        if ($u->hasRole('alumno') && !$u->hasRole('admin')) {
            $grupoDelAlumno = DB::table('grupo_integrantes')
                ->where('user_id', $u->id)->value('grupo_id');
            if ((int)$sol->grupo_id !== (int)$grupoDelAlumno) {
                abort(403);
            }
        }

        // Ítems: compatibilidad con solicitud_items (tipo_item + item_id)
        $items = DB::table('solicitud_items as si')
            ->leftJoin('insumos as i', function($j){
                $j->on('i.id','=','si.item_id')->where('si.tipo_item','INSUMO');
            })
            ->leftJoin('equipos as e', function($j){
                $j->on('e.id','=','si.item_id')->where('si.tipo_item','EQUIPO');
            })
            ->select(
                'si.id','si.solicitud_id','si.tipo_item','si.item_id','si.unidad',
                'si.cantidad_solic','si.cantidad_entregada','si.observacion',
                DB::raw("CASE WHEN si.tipo_item='INSUMO' THEN i.nombre
                            WHEN si.tipo_item='EQUIPO' THEN e.nombre
                            ELSE NULL END as item_nombre")
            )
            ->where('si.solicitud_id',$id)
            ->get();

        return ['solicitud' => $sol, 'items' => $items];
    }
    public function mias(Request $r)
    {
        $u = $r->user();
        $perPage = (int) $r->input('per_page', 12);

        // 1) Resolver grupos del usuario con múltiples estrategias
        $grupoIds = [];

        // a) Si existe grupo_integrantes, úsalo
        if (Schema::hasTable('grupo_integrantes')) {
            $grupoIds = DB::table('grupo_integrantes')
                ->where('user_id', $u->id)
                ->pluck('grupo_id')->all();
        }
        // b) Si no, si grupos tiene delegado_id, usa los grupos donde el usuario es delegado
        if (!$grupoIds && Schema::hasTable('grupos') && Schema::hasColumn('grupos','delegado_id')) {
            $grupoIds = DB::table('grupos')
                ->where('delegado_id', $u->id)
                ->pluck('id')->all();
        }

        // 2) Preferir la vista si existe
        if (Schema::hasTable('vw_tablon_laboratorio')) {
            $q = DB::table('vw_tablon_laboratorio as v');

            // Filtrar por grupo (si se resolvió alguno) o por autor (si la vista lo tuviera)
            if (!empty($grupoIds)) {
                $q->whereIn('v.grupo_id', $grupoIds);
            } elseif (Schema::hasColumn('vw_tablon_laboratorio','creado_por')) {
                $q->where('v.creado_por', $u->id);
            } else {
                // sin grupos ni creado_por en vista: mostrar solo lo del usuario vía solicitudes join
                $q->join('solicitudes as s','s.id','=','v.solicitud_id')
                ->where(function($w) use ($u) {
                    $w->where('s.creado_por',$u->id);
                    if (Schema::hasColumn('solicitudes','delegado_id')) {
                        $w->orWhere('s.delegado_id',$u->id);
                    }
                });
            }

            if ($r->filled('estado')) $q->where('v.estado',$r->estado);
            if ($r->filled('desde'))  $q->whereDate('v.creado_at','>=',$r->desde);
            if ($r->filled('hasta'))  $q->whereDate('v.creado_at','<=',$r->hasta);

            $orderCol = Schema::hasColumn('vw_tablon_laboratorio','actualizado_at') ? 'v.actualizado_at' : 'v.creado_at';
            return $q->orderByDesc(DB::raw($orderCol))
                    ->paginate($perPage)->withQueryString();
        }

        // 3) Fallback sin vista: usar solicitudes directo
        $q = DB::table('solicitudes as s');

        $q->where(function($w) use ($u, $grupoIds) {
            if (!empty($grupoIds)) {
                $w->whereIn('s.grupo_id', $grupoIds);
            }
            // además, todo lo que creó el usuario
            if (Schema::hasColumn('solicitudes','creado_por')) {
                $w->orWhere('s.creado_por', $u->id);
            }
            // y si existe delegado_id, también
            if (Schema::hasColumn('solicitudes','delegado_id')) {
                $w->orWhere('s.delegado_id', $u->id);
            }
        });

        if ($r->filled('estado') && Schema::hasColumn('solicitudes','estado')) {
            $q->where('s.estado',$r->estado);
        }
        if ($r->filled('desde') && Schema::hasColumn('solicitudes','creado_at')) {
            $q->whereDate('s.creado_at','>=',$r->desde);
        }
        if ($r->filled('hasta') && Schema::hasColumn('solicitudes','creado_at')) {
            $q->whereDate('s.creado_at','<=',$r->hasta);
        }

        $orderCol = Schema::hasColumn('solicitudes','actualizado_at') ? 's.actualizado_at'
                : (Schema::hasColumn('solicitudes','creado_at') ? 's.creado_at' : 's.id');

        return $q->orderByDesc(DB::raw($orderCol))
                ->paginate($perPage)->withQueryString();
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
    public function index(Request $r)
    {
        $u = $r->user();
        $perPage = (int) ($r->input('per_page', 12));

        /* 1) Si existe la vista vw_tablon_laboratorio, la usamos */
        if (Schema::hasTable('vw_tablon_laboratorio')) {
            $q = DB::table('vw_tablon_laboratorio as v');

            // Scope por profesor (solo si podemos unir cursos)
            if ($u->hasRole('profesor') && !$u->hasAnyRole(['admin','tecnico'])) {
                if (Schema::hasTable('cursos') && Schema::hasColumn('cursos','profesor_id')) {
                    $q->join('cursos as c','c.id','=','v.curso_id')
                    ->where('c.profesor_id',$u->id);
                } elseif (Schema::hasTable('curso_docentes')) {
                    $q->join('curso_docentes as cd','cd.curso_id','=','v.curso_id')
                    ->where('cd.user_id',$u->id);
                }
            }

            if ($r->filled('q')) {
                $like = '%'.$r->q.'%';
                $q->where(function($w) use($like){
                    $w->where('v.grupo_nombre','like',$like)
                    ->orWhere('v.practica_titulo','like',$like)
                    ->orWhere('v.laboratorio_nombre','like',$like);
                });
            }
            if ($r->filled('estado'))     $q->where('v.estado',$r->estado);
            if ($r->filled('curso_id'))   $q->where('v.curso_id',$r->curso_id);
            if ($r->filled('seccion_id')) $q->where('v.seccion_id',$r->seccion_id);
            if ($r->filled('seccion'))    $q->where('v.seccion_nombre','like','%'.$r->seccion.'%');
            if ($r->filled('desde'))      $q->whereDate('v.creado_at','>=',$r->desde);
            if ($r->filled('hasta'))      $q->whereDate('v.creado_at','<=',$r->hasta);

            $orderCol = Schema::hasColumn('vw_tablon_laboratorio','actualizado_at')
                ? 'v.actualizado_at' : 'v.creado_at';
            $q->orderByDesc(DB::raw($orderCol));

            return $q->paginate($perPage)->withQueryString();
        }

        /* 2) Fallback (si no existiera la vista): usar solo solicitudes + laboratorio */
        $q = DB::table('solicitudes as s');

        if (Schema::hasColumn('solicitudes','laboratorio_id')) {
            $q->leftJoin('laboratorios as l','l.id','=','s.laboratorio_id');
        }

        $selects = ['s.*'];
        if (Schema::hasColumn('solicitudes','laboratorio_id')) {
            $selects[] = 'l.nombre as laboratorio_nombre';
        }
        $q->select($selects);

        if ($r->filled('estado') && Schema::hasColumn('solicitudes','estado')) {
            $q->where('s.estado',$r->estado);
        }
        if ($r->filled('desde') && Schema::hasColumn('solicitudes','creado_at')) {
            $q->whereDate('s.creado_at','>=',$r->desde);
        }
        if ($r->filled('hasta') && Schema::hasColumn('solicitudes','creado_at')) {
            $q->whereDate('s.creado_at','<=',$r->hasta);
        }

        $orderCol = Schema::hasColumn('solicitudes','actualizado_at') ? 's.actualizado_at'
                : (Schema::hasColumn('solicitudes','creado_at') ? 's.creado_at' : 's.id');
        $q->orderByDesc(DB::raw($orderCol));

        return $q->paginate($perPage)->withQueryString();
    }

}
