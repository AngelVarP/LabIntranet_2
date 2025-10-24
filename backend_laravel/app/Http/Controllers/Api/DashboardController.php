<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function kpis(Request $r){
        $hoy = now()->toDateString();
        $en30 = now()->addDays(30)->toDateString();

        // estados de solicitudes
        $solEstados = DB::table('solicitudes')
            ->select('estado', DB::raw('COUNT(*) as total'))
            ->groupBy('estado')
            ->pluck('total','estado');

        // caducidad próxima (<= 30 días)
        $caducan = DB::table('insumo_lotes')
            ->whereNotNull('caducidad')
            ->whereBetween('caducidad', [$hoy, $en30])
            ->where('cantidad','>',0)
            ->count();

        // bajo mínimo (stock total < stock_minimo)
        $stock = DB::table('insumo_lotes')
            ->select('insumo_id', DB::raw('SUM(cantidad) as s'))
            ->groupBy('insumo_id');
        $bajoMinimo = DB::table('insumos as i')
            ->leftJoinSub($stock,'st',fn($j)=>$j->on('st.insumo_id','=','i.id'))
            ->whereRaw('COALESCE(st.s,0) < i.stock_minimo')
            ->count();

        // préstamos
        $prestamosAbiertos = DB::table('prestamos')->whereIn('estado',['ABIERTO','PARCIAL'])->count();

        // entregas hechas hoy (kardex por EGRESO y motivo ENTREGA_SOLICITUD)
        $entregasHoy = DB::table('kardex_movimientos')
            ->whereDate('fecha',$hoy)
            ->where('tipo_item','INSUMO')
            ->where('tipo_mov','EGRESO')
            ->where('motivo','ENTREGA_SOLICITUD')
            ->count();

        return [
            'solicitudes' => [
                'PENDIENTE' => (int)($solEstados['PENDIENTE'] ?? 0),
                'APROBADO'  => (int)($solEstados['APROBADO'] ?? 0),
                'RECHAZADO' => (int)($solEstados['RECHAZADO'] ?? 0),
                'PREPARADO' => (int)($solEstados['PREPARADO'] ?? 0),
                'ENTREGADO' => (int)($solEstados['ENTREGADO'] ?? 0),
                'CERRADO'   => (int)($solEstados['CERRADO'] ?? 0),
            ],
            'alertas' => [
                'caducan_30d' => $caducan,
                'bajo_minimo' => $bajoMinimo,
            ],
            'prestamos_abiertos' => $prestamosAbiertos,
            'entregas_hoy'       => $entregasHoy,
            'fecha'              => $hoy,
        ];
    }

    // Serie por día de solicitudes creadas y entregas (para un gráfico de líneas)
    public function series(Request $r){
        $desde = $r->query('desde') ?: now()->subDays(14)->toDateString();
        $hasta = $r->query('hasta') ?: now()->toDateString();

        // solicitudes creadas por día
        $solicitudesDia = DB::table('solicitudes')
            ->selectRaw('DATE(creado_at) d, COUNT(*) c')
            ->whereBetween('creado_at', [$desde.' 00:00:00', $hasta.' 23:59:59'])
            ->groupBy('d')->orderBy('d')
            ->get();

        // entregas por día (kardex EGRESO ENTREGA_SOLICITUD)
        $entregasDia = DB::table('kardex_movimientos')
            ->selectRaw('DATE(fecha) d, COUNT(*) c')
            ->whereBetween('fecha', [$desde.' 00:00:00', $hasta.' 23:59:59'])
            ->where('tipo_item','INSUMO')->where('tipo_mov','EGRESO')->where('motivo','ENTREGA_SOLICITUD')
            ->groupBy('d')->orderBy('d')->get();

        return [
            'rango'=> compact('desde','hasta'),
            'solicitudes' => $solicitudesDia,
            'entregas'    => $entregasDia,
        ];
    }
    public function resumen(Request $r){
        // Conteo de solicitudes por estado (últimos 30 días, ajustable)
        $desde = $r->query('desde'); // opcional YYYY-MM-DD
        $hasta = $r->query('hasta'); // opcional YYYY-MM-DD

        $solQ = DB::table('solicitudes');
        if($desde) $solQ->where('creado_at','>=',$desde.' 00:00:00');
        if($hasta) $solQ->where('creado_at','<=',$hasta.' 23:59:59');

        $solicitudesPorEstado = $solQ->groupBy('estado')
            ->select('estado', DB::raw('COUNT(*) as total'))
            ->pluck('total','estado');

        // Insumos bajo mínimo (conteo)
        $stockSub = DB::table('insumo_lotes')
            ->select('insumo_id', DB::raw('SUM(cantidad) as stock_total'))
            ->groupBy('insumo_id');

        $bajoMinimo = DB::table('insumos as i')
            ->leftJoinSub($stockSub,'st',fn($j)=>$j->on('st.insumo_id','=','i.id'))
            ->whereRaw('COALESCE(st.stock_total,0) < COALESCE(i.stock_minimo,0)')
            ->count();

        // Préstamos abiertos
        $prestamosAbiertos = DB::table('prestamos')->whereIn('estado',['ABIERTO','PARCIAL'])->count();

        // Mantenimientos programados (si no usas esta tabla, quedará 0)
        $mantenProg = DB::table('mantenimientos')->where('estado','PROGRAMADO')->count();

        return [
            'solicitudes_por_estado' => $solicitudesPorEstado,
            'insumos_bajo_minimo'    => $bajoMinimo,
            'prestamos_abiertos'     => $prestamosAbiertos,
            'mantenimientos_programados' => $mantenProg,
        ];
    }
}
