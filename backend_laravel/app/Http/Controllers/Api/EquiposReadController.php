<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class EquiposReadController extends Controller
{
    // 1) DETALLE COMPLETO DEL EQUIPO (cabecera + accesorios + préstamo actual + últimos mantenimientos)
    public function detalle(Request $r, int $id)
    {
        $u = $r->user(); if(!$u) abort(401);

        // Cabecera
        $eq = DB::table('equipos as e')
            ->leftJoin('laboratorios as l','l.id','=','e.laboratorio_id')
            ->where('e.id',$id)
            ->first([
                'e.id','e.codigo','e.nombre','e.modelo','e.serie','e.estado',
                'e.laboratorio_id','l.nombre as laboratorio'
            ]);
        if(!$eq) abort(404,'Equipo no encontrado');

        // Accesorios
        $accesorios = DB::table('equipos_accesorios')
            ->where('equipo_id',$id)
            ->orderBy('nombre')
            ->get(['id','nombre','cantidad']);

        // Préstamo actual (si está en un préstamo ABIERTO/PARCIAL)
        $prestamoActual = DB::table('prestamo_items as pi')
            ->join('prestamos as p','p.id','=','pi.prestamo_id')
            ->leftJoin('users as u2','u2.id','=','p.responsable_id')
            ->where('pi.equipo_id',$id)
            ->whereIn('p.estado',['ABIERTO','PARCIAL'])
            ->orderByDesc('p.fecha_prestamo')
            ->first([
                'p.id as prestamo_id','p.estado','p.fecha_prestamo','p.fecha_compromiso',
                'p.responsable_id','u2.name as responsable'
            ]);

        // Últimos mantenimientos (top 5)
        $ultMto = DB::table('mantenimientos as m')
            ->where('m.equipo_id',$id)
            ->orderByDesc(DB::raw('COALESCE(m.fecha_realizada,m.fecha_programada)'))
            ->limit(5)
            ->get([
                'm.id','m.tipo','m.estado','m.fecha_programada','m.fecha_realizada','m.detalle'
            ]);

        return [
            'equipo'        => $eq,
            'accesorios'    => $accesorios,
            'prestamo_actual'=> $prestamoActual,   // null si no tiene
            'ult_mantenimientos' => $ultMto,
        ];
    }

    // 2) KARDEX DEL EQUIPO (paginado)
    public function kardex(Request $r, int $id)
    {
        $r->validate([
            'desde' => 'nullable|date',
            'hasta' => 'nullable|date',
        ]);
        $per = max(10, min((int)$r->query('per_page',50), 200));

        $q = DB::table('kardex_movimientos as k')
            ->leftJoin('laboratorios as l','l.id','=','k.laboratorio_id')
            ->where('k.tipo_item','EQUIPO')
            ->where('k.item_id',$id)
            ->when($r->filled('desde'), fn($qq)=>$qq->where('k.fecha','>=',$r->desde.' 00:00:00'))
            ->when($r->filled('hasta'), fn($qq)=>$qq->where('k.fecha','<=',$r->hasta.' 23:59:59'))
            ->orderByDesc('k.fecha');

        return $q->paginate($per, [
            'k.id','k.fecha','l.nombre as laboratorio','k.tipo_mov','k.motivo','k.referencia','k.usuario_id'
        ]);
    }

    // 3) MANTENIMIENTOS DEL EQUIPO (lista filtrable)
    public function mantenimientos(Request $r, int $id)
    {
        $r->validate([
            'estado' => 'nullable|in:PROGRAMADO,EN_PROCESO,COMPLETADO,ANULADO',
            'desde'  => 'nullable|date',
            'hasta'  => 'nullable|date',
        ]);
        $per = max(10, min((int)$r->query('per_page',50), 200));

        $q = DB::table('mantenimientos as m')
            ->where('m.equipo_id',$id)
            ->when($r->filled('estado'), fn($qq)=>$qq->where('m.estado',$r->estado))
            ->when($r->filled('desde'),  fn($qq)=>$qq->where('m.fecha_programada','>=',$r->desde))
            ->when($r->filled('hasta'),  fn($qq)=>$qq->where('m.fecha_programada','<=',$r->hasta))
            ->orderByDesc(DB::raw('COALESCE(m.fecha_realizada,m.fecha_programada)'));

        return $q->paginate($per, [
            'm.id','m.tipo','m.estado','m.fecha_programada','m.fecha_realizada','m.detalle',
            'm.costo_estimado','m.costo_real','m.creado_at','m.actualizado_at'
        ]);
    }
}
