<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class DevolucionesController extends Controller
{
    public function detalle(Request $r, int $id)
    {
        // Cabecera de equipo
        $eq = DB::table('equipos as e')
            ->leftJoin('laboratorios as l','l.id','=','e.laboratorio_id')
            ->where('e.id',$id)
            ->select('e.id','e.codigo','e.nombre','e.modelo','e.serie','e.estado',
                    'e.laboratorio_id','l.nombre as laboratorio')
            ->first();
        if(!$eq) abort(404,'Equipo no encontrado');

        // Accesorios
        $accesorios = DB::table('equipos_accesorios')
            ->where('equipo_id',$id)
            ->orderBy('nombre')
            ->get(['id','nombre','cantidad']);

        // Préstamo activo (si existe)
        $prestamoActivo = DB::table('prestamos as p')
            ->join('prestamo_items as pi','pi.prestamo_id','=','p.id')
            ->leftJoin('users as u','u.id','=','p.responsable_id')
            ->whereIn('p.estado',['ABIERTO','PARCIAL'])
            ->where('pi.equipo_id',$id)
            ->orderByDesc('p.fecha_prestamo')
            ->first([
                'p.id as prestamo_id','p.estado','p.fecha_prestamo','p.fecha_compromiso',
                'p.responsable_id','u.name as responsable'
            ]);

        // Mantenimientos (últimos 10)
        $mantenimientos = DB::table('mantenimientos')
            ->where('equipo_id',$id)
            ->orderByDesc(DB::raw('COALESCE(fecha_realizada, fecha_programada)'))
            ->limit(10)
            ->get(['id','tipo','estado','fecha_programada','fecha_realizada','detalle','costo_estimado','costo_real']);

        // Kardex (si registras movimientos de equipo), últimos 20
        $kardex = DB::table('kardex_movimientos as k')
            ->leftJoin('laboratorios as l','l.id','=','k.laboratorio_id')
            ->leftJoin('users as u','u.id','=','k.usuario_id')
            ->where('k.tipo_item','EQUIPO')
            ->where('k.item_id',$id)
            ->orderByDesc('k.fecha')
            ->limit(20)
            ->get([
                'k.id','k.fecha','k.tipo_mov','k.motivo','k.referencia',
                'l.nombre as laboratorio','u.name as usuario'
            ]);

        return [
            'equipo'            => $eq,
            'accesorios'        => $accesorios,
            'prestamo_activo'   => $prestamoActivo,
            'mantenimientos'    => $mantenimientos,
            'kardex_reciente'   => $kardex,
        ];
    }





      
}
