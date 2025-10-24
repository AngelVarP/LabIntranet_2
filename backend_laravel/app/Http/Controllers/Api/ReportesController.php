<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportesController extends Controller
{
    public function devolucionesCsv(Request $r): StreamedResponse
    {
        $r->validate([
            'laboratorio_id' => 'nullable|integer',
            'equipo_id'      => 'nullable|integer',
            'responsable_id' => 'nullable|integer',
            'estado_equipo'  => 'nullable|in:OK,DANADO,FALTANTE',
            'desde'          => 'nullable|date',
            'hasta'          => 'nullable|date',
        ]);

        $rows = DB::table('devoluciones as d')
            ->join('prestamos as p','p.id','=','d.prestamo_id')
            ->leftJoin('users as ur','ur.id','=','p.responsable_id')
            ->join('equipos as e','e.id','=','d.equipo_id')
            ->leftJoin('laboratorios as l','l.id','=','e.laboratorio_id')
            ->when($r->filled('laboratorio_id'), fn($q)=>$q->where('e.laboratorio_id',$r->laboratorio_id))
            ->when($r->filled('equipo_id'),      fn($q)=>$q->where('d.equipo_id',$r->equipo_id))
            ->when($r->filled('responsable_id'), fn($q)=>$q->where('p.responsable_id',$r->responsable_id))
            ->when($r->filled('estado_equipo'),  fn($q)=>$q->where('d.estado_equipo',$r->estado_equipo))
            ->when($r->filled('desde'),          fn($q)=>$q->where('d.fecha_dev','>=',$r->desde.' 00:00:00'))
            ->when($r->filled('hasta'),          fn($q)=>$q->where('d.fecha_dev','<=',$r->hasta.' 23:59:59'))
            ->orderByDesc('d.fecha_dev')
            ->get([
                'd.id','d.fecha_dev','d.estado_equipo','d.observacion',
                'e.id as equipo_id','e.codigo as equipo_codigo','e.nombre as equipo_nombre','e.modelo as equipo_modelo',
                'l.nombre as laboratorio',
                'p.id as prestamo_id','p.estado as prestamo_estado','p.fecha_prestamo','p.fecha_compromiso',
                'ur.name as responsable','p.responsable_id'
            ]);

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="devoluciones.csv"',
        ];

        return response()->stream(function() use ($rows){
            $out = fopen('php://output','w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM
            fputcsv($out, [
                'Devolución ID','Fecha devolución','Estado equipo','Observación',
                'Equipo ID','Equipo código','Equipo nombre','Modelo','Laboratorio',
                'Préstamo ID','Estado préstamo','Fecha préstamo','Fecha compromiso',
                'Responsable','Responsable ID'
            ]);
            foreach($rows as $r){
                fputcsv($out, [
                    $r->id, $r->fecha_dev, $r->estado_equipo, $r->observacion,
                    $r->equipo_id, $r->equipo_codigo, $r->equipo_nombre, $r->equipo_modelo,
                    $r->laboratorio,
                    $r->prestamo_id, $r->prestamo_estado, $r->fecha_prestamo, $r->fecha_compromiso,
                    $r->responsable, $r->responsable_id
                ]);
            }
            fclose($out);
        }, 200, $headers);
    }

    public function stockPdf(Request $r){
        $labId = $r->query('laboratorio_id'); // opcional
        $stock = DB::table('insumos as i')
            ->leftJoin('categorias_insumo as c','c.id','=','i.categoria_id')
            ->leftJoin(DB::raw('(SELECT insumo_id, SUM(cantidad) s FROM insumo_lotes GROUP BY insumo_id) t'),'t.insumo_id','=','i.id')
            ->when($labId, fn($q)=>$q->join('insumo_lotes as il','il.insumo_id','=','i.id')->where('il.laboratorio_id',$labId))
            ->select('i.codigo','i.nombre','i.unidad','i.stock_minimo as minimo','c.nombre as categoria', DB::raw('COALESCE(t.s,0) as stock'))
            ->orderBy('c.nombre')->orderBy('i.nombre')
            ->get();

        $pdf = Pdf::loadView('pdf.stock', ['rows'=>$stock, 'titulo'=>'Stock por laboratorio']);
        return $pdf->download('stock.pdf');
    }
    public function solicitudesPdf(Request $r){
        $r->validate([
            'laboratorio_id'=>'nullable|integer',
            'estado'=>'nullable|in:BORRADOR,PENDIENTE,APROBADO,RECHAZADO,PREPARADO,ENTREGADO,CERRADO',
            'desde'=>'nullable|date',
            'hasta'=>'nullable|date',
        ]);

        $q = DB::table('solicitudes as s')
            ->join('laboratorios as l','l.id','=','s.laboratorio_id')
            ->join('grupos as g','g.id','=','s.grupo_id')
            ->join('practicas as p','p.id','=','s.practica_id')
            ->join('secciones as sec','sec.id','=','p.seccion_id')
            ->join('cursos as c','c.id','=','sec.curso_id')
            ->select([
                's.id','s.estado','s.prioridad','s.creado_at','s.actualizado_at',
                'l.nombre as laboratorio','g.nombre as grupo',
                'p.titulo as practica','sec.nombre as seccion','c.nombre as curso'
            ])
            ->when($r->filled('laboratorio_id'), fn($qq)=>$qq->where('s.laboratorio_id',$r->laboratorio_id))
            ->when($r->filled('estado'),         fn($qq)=>$qq->where('s.estado',$r->estado))
            ->when($r->filled('desde'),          fn($qq)=>$qq->where('s.creado_at','>=',$r->desde.' 00:00:00'))
            ->when($r->filled('hasta'),          fn($qq)=>$qq->where('s.creado_at','<=',$r->hasta.' 23:59:59'))
            ->orderByDesc('s.creado_at');

        $rows = $q->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.solicitudes', [
            'rows'=>$rows,
            'titulo'=>'Solicitudes',
            'filtros'=>[
                'laboratorio_id'=>$r->laboratorio_id,
                'estado'=>$r->estado,
                'desde'=>$r->desde,
                'hasta'=>$r->hasta,
            ],
        ]);
        return $pdf->download('solicitudes.pdf');
    }
    public function insumosCsv(Request $r): StreamedResponse
    {
        $q     = trim((string)$r->query('q',''));
        $catId = $r->query('categoria_id');

        $stockSub = DB::table('insumo_lotes')
            ->select('insumo_id', DB::raw('SUM(cantidad) as stock_total'))
            ->groupBy('insumo_id');

        $rows = DB::table('insumos as i')
            ->leftJoin('categorias_insumo as c','c.id','=','i.categoria_id')
            ->leftJoinSub($stockSub,'st',fn($j)=>$j->on('st.insumo_id','=','i.id'))
            ->when($q !== '', function($qq) use($q){
                $like='%'.$q.'%';
                $qq->where(fn($w)=>$w->where('i.nombre','like',$like)->orWhere('i.codigo','like',$like));
            })
            ->when($catId, fn($qq)=>$qq->where('i.categoria_id',$catId))
            ->orderBy('i.nombre')
            ->get([
                'i.codigo','i.nombre','i.unidad','i.stock_minimo','i.activo',
                'c.nombre as categoria',
                DB::raw('COALESCE(st.stock_total,0) as stock_total')
            ]);

        $headers = [
            'Content-Type'=>'text/csv; charset=UTF-8',
            'Content-Disposition'=>'attachment; filename="insumos.csv"',
        ];

        return response()->stream(function() use ($rows){
            $out = fopen('php://output','w');
            fwrite($out,"\xEF\xBB\xBF"); // BOM para Excel
            fputcsv($out,['Codigo','Nombre','Categoria','Unidad','Stock total','Stock mínimo','Bajo mínimo','Activo']);
            foreach($rows as $r){
                $bajo = ((float)$r->stock_total < (float)$r->stock_minimo) ? 'SI' : 'NO';
                fputcsv($out,[
                    $r->codigo, $r->nombre, $r->categoria, $r->unidad,
                    (string)$r->stock_total, (string)$r->stock_minimo, $bajo, $r->activo?'SI':'NO'
                ]);
            }
            fclose($out);
        },200,$headers);
    }
    public function solicitudesCsv(Request $r): StreamedResponse
    {
        $r->validate([
            'laboratorio_id'=>'nullable|integer',
            'estado'=>'nullable|in:BORRADOR,PENDIENTE,APROBADO,RECHAZADO,PREPARADO,ENTREGADO,CERRADO',
            'desde'=>'nullable|date',
            'hasta'=>'nullable|date',
        ]);

        $rows = DB::table('solicitudes as s')
            ->join('laboratorios as l','l.id','=','s.laboratorio_id')
            ->join('grupos as g','g.id','=','s.grupo_id')
            ->join('practicas as p','p.id','=','s.practica_id')
            ->join('secciones as sec','sec.id','=','p.seccion_id')
            ->join('cursos as c','c.id','=','sec.curso_id')
            ->when($r->filled('laboratorio_id'), fn($qq)=>$qq->where('s.laboratorio_id',$r->laboratorio_id))
            ->when($r->filled('estado'),         fn($qq)=>$qq->where('s.estado',$r->estado))
            ->when($r->filled('desde'),          fn($qq)=>$qq->where('s.creado_at','>=',$r->desde.' 00:00:00'))
            ->when($r->filled('hasta'),          fn($qq)=>$qq->where('s.creado_at','<=',$r->hasta.' 23:59:59'))
            ->orderByDesc('s.creado_at')
            ->get([
                's.id','s.estado','s.prioridad','s.creado_at','s.actualizado_at',
                'l.nombre as laboratorio','g.nombre as grupo',
                'p.titulo as practica','sec.nombre as seccion','c.nombre as curso'
            ]);

        $headers = [
            'Content-Type'=>'text/csv; charset=UTF-8',
            'Content-Disposition'=>'attachment; filename="solicitudes.csv"',
        ];

        return response()->stream(function() use ($rows){
            $out = fopen('php://output','w');
            fwrite($out,"\xEF\xBB\xBF");
            fputcsv($out,['ID','Estado','Prioridad','Laboratorio','Curso','Sección','Práctica','Grupo','Creado','Actualizado']);
            foreach($rows as $r){
                fputcsv($out,[
                    $r->id,$r->estado,$r->prioridad,$r->laboratorio,$r->curso,$r->seccion,$r->practica,$r->grupo,$r->creado_at,$r->actualizado_at
                ]);
            }
            fclose($out);
        },200,$headers);
    }
    public function kardexCsv(Request $r): StreamedResponse
    {
        $r->validate([
            'laboratorio_id' => 'nullable|integer',
            'tipo_item'      => 'nullable|in:INSUMO,EQUIPO',
            'item_id'        => 'nullable|integer',
            'tipo_mov'       => 'nullable|in:INGRESO,EGRESO,AJUSTE',
            'desde'          => 'nullable|date',
            'hasta'          => 'nullable|date',
        ]);

        $rows = DB::table('kardex_movimientos as k')
            ->join('laboratorios as l','l.id','=','k.laboratorio_id')
            ->when($r->filled('laboratorio_id'), fn($q)=>$q->where('k.laboratorio_id',$r->laboratorio_id))
            ->when($r->filled('tipo_item'),      fn($q)=>$q->where('k.tipo_item',$r->tipo_item))
            ->when($r->filled('item_id'),        fn($q)=>$q->where('k.item_id',$r->item_id))
            ->when($r->filled('tipo_mov'),       fn($q)=>$q->where('k.tipo_mov',$r->tipo_mov))
            ->when($r->filled('desde'),          fn($q)=>$q->where('k.fecha','>=',$r->desde.' 00:00:00'))
            ->when($r->filled('hasta'),          fn($q)=>$q->where('k.fecha','<=',$r->hasta.' 23:59:59'))
            ->orderByDesc('k.fecha')
            ->get([
                'k.fecha','l.nombre as laboratorio','k.tipo_item','k.item_id',
                'k.tipo_mov','k.cantidad','k.motivo','k.referencia','k.usuario_id'
            ]);

        $headers = [
            'Content-Type'=>'text/csv; charset=UTF-8',
            'Content-Disposition'=>'attachment; filename="kardex.csv"',
        ];

        return response()->stream(function() use ($rows){
            $out = fopen('php://output','w');
            fwrite($out,"\xEF\xBB\xBF");
            fputcsv($out,['Fecha','Laboratorio','Tipo ítem','Item ID','Movimiento','Cantidad','Motivo','Referencia','Usuario ID']);
            foreach($rows as $r){
                fputcsv($out,[
                    $r->fecha,$r->laboratorio,$r->tipo_item,$r->item_id,
                    $r->tipo_mov,$r->cantidad,$r->motivo,$r->referencia,$r->usuario_id
                ]);
            }
            fclose($out);
        },200,$headers);
    }
    public function prestamosCsv(Request $r): StreamedResponse
    {
        $r->validate([
            'estado'         => 'nullable|in:ABIERTO,PARCIAL,CERRADO',
            'responsable_id' => 'nullable|integer',
            'desde'          => 'nullable|date',
            'hasta'          => 'nullable|date',
        ]);

        $rows = DB::table('prestamos as p')
            ->leftJoin('users as u','u.id','=','p.responsable_id')
            ->when($r->filled('estado'),         fn($q)=>$q->where('p.estado',$r->estado))
            ->when($r->filled('responsable_id'), fn($q)=>$q->where('p.responsable_id',$r->responsable_id))
            ->when($r->filled('desde'),          fn($q)=>$q->where('p.fecha_prestamo','>=',$r->desde.' 00:00:00'))
            ->when($r->filled('hasta'),          fn($q)=>$q->where('p.fecha_prestamo','<=',$r->hasta.' 23:59:59'))
            ->orderByDesc('p.fecha_prestamo')
            ->get([
                'p.id','p.estado','p.fecha_prestamo','p.fecha_compromiso',
                'u.name as responsable','p.responsable_id'
            ]);

        $headers = [
            'Content-Type'=>'text/csv; charset=UTF-8',
            'Content-Disposition'=>'attachment; filename="prestamos.csv"',
        ];

        return response()->stream(function() use ($rows){
            $out = fopen('php://output','w');
            fwrite($out,"\xEF\xBB\xBF");
            fputcsv($out,['ID','Estado','Fecha préstamo','Fecha compromiso','Responsable','Responsable ID']);
            foreach($rows as $r){
                fputcsv($out,[
                    $r->id,$r->estado,$r->fecha_prestamo,$r->fecha_compromiso,$r->responsable,$r->responsable_id
                ]);
            }
            fclose($out);
        },200,$headers);
    }
    public function insumosBajoMinimoJson(\Illuminate\Http\Request $r){
        $q     = trim((string)$r->query('q',''));
        $catId = $r->query('categoria_id');

        $stockSub = DB::table('insumo_lotes')
            ->select('insumo_id', DB::raw('SUM(cantidad) as stock_total'))
            ->groupBy('insumo_id');

        $rows = DB::table('insumos as i')
            ->leftJoin('categorias_insumo as c','c.id','=','i.categoria_id')
            ->leftJoinSub($stockSub,'st',fn($j)=>$j->on('st.insumo_id','=','i.id'))
            ->when($q !== '', function($qq) use($q){
                $like='%'.$q.'%';
                $qq->where(fn($w)=>$w->where('i.nombre','like',$like)->orWhere('i.codigo','like',$like));
            })
            ->when($catId, fn($qq)=>$qq->where('i.categoria_id',$catId))
            ->whereRaw('COALESCE(st.stock_total,0) < COALESCE(i.stock_minimo,0)')
            ->orderBy('i.nombre')
            ->get([
                'i.id','i.codigo','i.nombre','i.unidad','i.stock_minimo',
                DB::raw('COALESCE(st.stock_total,0) as stock_total'),
                'c.nombre as categoria'
            ]);

        return $rows;
    }
    public function devolucionesIncidencias(Request $r)
    {
        $r->validate([
            'laboratorio_id' => 'nullable|integer',
            'desde'          => 'nullable|date',
            'hasta'          => 'nullable|date',
        ]);

        $rows = DB::table('devoluciones as d')
            ->join('equipos as e','e.id','=','d.equipo_id')
            ->leftJoin('laboratorios as l','l.id','=','e.laboratorio_id')
            ->join('prestamos as p','p.id','=','d.prestamo_id')
            ->leftJoin('users as ur','ur.id','=','p.responsable_id')
            ->whereIn('d.estado_equipo',['DANADO','FALTANTE'])
            ->when($r->filled('laboratorio_id'), fn($q)=>$q->where('e.laboratorio_id',$r->laboratorio_id))
            ->when($r->filled('desde'),          fn($q)=>$q->where('d.fecha_dev','>=',$r->desde.' 00:00:00'))
            ->when($r->filled('hasta'),          fn($q)=>$q->where('d.fecha_dev','<=',$r->hasta.' 23:59:59'))
            ->orderByDesc('d.fecha_dev')
            ->get([
                'd.id as devolucion_id','d.fecha_dev','d.estado_equipo','d.observacion',
                'e.id as equipo_id','e.codigo as equipo_codigo','e.nombre as equipo_nombre','e.modelo',
                'l.nombre as laboratorio',
                'p.id as prestamo_id','ur.name as responsable','p.responsable_id'
            ]);

        // Resumen por tipo de incidencia
        $resumen = [
            'DANADO'   => (int) $rows->where('estado_equipo','DANADO')->count(),
            'FALTANTE' => (int) $rows->where('estado_equipo','FALTANTE')->count(),
            'TOTAL'    => (int) $rows->count(),
        ];

        return [
            'resumen'     => $resumen,
            'incidencias' => $rows,
        ];
    }




}
