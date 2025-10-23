<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class TablonController extends Controller
{
    public function index(Request $request)
    {
        $labId   = $request->query('laboratorio_id');
        $estado  = $request->query('estado');
        $cursoId = $request->query('curso_id');
        $q       = $request->query('q');

        $query = DB::table('vw_tablon_laboratorio as v')
            ->select([
                'v.solicitud_id',
                'v.laboratorio_id',
                'v.laboratorio_nombre',
                'v.estado',
                'v.prioridad',
                'v.creado_at',
                'v.actualizado_at',
                'v.grupo_id',
                'v.grupo_nombre',
                'v.practica_id',
                'v.practica_titulo',
                'v.seccion_id',
                'v.seccion_nombre',
                'v.curso_id',
                'v.curso_nombre',
            ])
            ->when($labId, fn($q2)=>$q2->where('v.laboratorio_id',$labId))
            ->when($estado, fn($q2)=>$q2->where('v.estado',$estado))
            ->when($cursoId, fn($q2)=>$q2->where('v.curso_id',$cursoId))
            ->when($q, function($q2) use($q){
                $like = '%'.$q.'%';
                $q2->where(function($w) use($like){
                    $w->where('v.practica_titulo','like',$like)
                      ->orWhere('v.grupo_nombre','like',$like)
                      ->orWhere('v.curso_nombre','like',$like)
                      ->orWhere('v.seccion_nombre','like',$like);
                });
            })
            ->orderByDesc('v.actualizado_at');

        $perPage = (int)($request->query('per_page',10));
        $perPage = max(1, min($perPage,100));

        return $query->paginate($perPage);
    }
}
