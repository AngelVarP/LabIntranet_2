<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SolicitudesReadController extends Controller
{
    public function show(Request $r, int $id)
    {
        $u = $r->user(); if(!$u) abort(401);

        // --- Cabecera de Solicitud + joins básicos
        $s = DB::table('solicitudes as s')
            ->join('laboratorios as l','l.id','=','s.laboratorio_id')
            ->join('grupos as g','g.id','=','s.grupo_id')
            ->join('practicas as p','p.id','=','s.practica_id')
            ->join('secciones as sec','sec.id','=','p.seccion_id')
            ->join('cursos as c','c.id','=','sec.curso_id')
            ->leftJoin('users as ud','ud.id','=','s.delegado_id')
            ->leftJoin('users as uc','uc.id','=','s.creado_por')
            ->where('s.id',$id)
            ->first([
                's.id','s.estado','s.prioridad','s.creado_at','s.actualizado_at',
                's.laboratorio_id','l.nombre as laboratorio',
                's.grupo_id','g.nombre as grupo',
                's.delegado_id','ud.name as delegado_nombre','ud.email as delegado_email',
                's.practica_id','p.titulo as practica','sec.id as seccion_id','sec.nombre as seccion',
                'c.id as curso_id','c.nombre as curso',
                'uc.name as creado_por_nombre'
            ]);

        if(!$s) abort(404);

        // --- Autorización mínima (no rompemos nada):
        // admin/tecnico/profesor ven todo; delegado ve su solicitud; otros => 403
        $allow = $u->hasAnyRole(['admin','tecnico','profesor']) || ($s->delegado_id && $s->delegado_id === $u->id);
        if(!$allow) abort(403,'No autorizado');

        // --- Items (separados por tipo) + totales
        $items = DB::table('solicitud_items as si')
            ->where('si.solicitud_id',$id)
            ->orderBy('si.tipo_item')
            ->orderBy('si.id')
            ->get([
                'si.id','si.tipo_item','si.item_id','si.unidad',
                DB::raw('COALESCE(si.cantidad_solic,0) as cantidad_solic'),
                DB::raw('COALESCE(si.cantidad_entregada,0) as cantidad_entregada'),
                'si.observacion'
            ]);

        $insumos = $items->where('tipo_item','INSUMO')->values();
        $equipos = $items->where('tipo_item','EQUIPO')->values();

        $tot = [
            'solicitado' => (float) $items->sum('cantidad_solic'),
            'entregado'  => (float) $items->sum('cantidad_entregada'),
        ];
        $tot['pendiente'] = max(0.0, $tot['solicitado'] - $tot['entregado']);
        $tot['avance']    = $tot['solicitado'] > 0 ? round(($tot['entregado']/$tot['solicitado'])*100, 1) : 0.0;

        // --- Historial de estados (línea de tiempo)
        $historial = DB::table('solicitud_estados_historial as h')
            ->leftJoin('users as u2','u2.id','=','h.usuario_id')
            ->where('h.solicitud_id',$id)
            ->orderBy('h.creado_at')
            ->get([
                'h.id','h.estado','h.comentario','h.creado_at',
                'u2.name as usuario'
            ]);

        // --- Comentarios (chat corto)
        $comentarios = DB::table('comentarios as cmt')
            ->leftJoin('users as u3','u3.id','=','cmt.autor_id')
            ->where('cmt.solicitud_id',$id)
            ->orderBy('cmt.creado_at')
            ->get([
                'cmt.id','cmt.texto','cmt.creado_at','u3.name as autor'
            ]);

        return [
            'solicitud'   => $s,
            'totales'     => $tot,
            'items'       => [
                'insumos' => $insumos,
                'equipos' => $equipos,
            ],
            'historial'   => $historial,
            'comentarios' => $comentarios,
        ];
    }
}
