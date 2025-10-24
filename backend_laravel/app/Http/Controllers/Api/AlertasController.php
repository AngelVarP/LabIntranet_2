<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class AlertasController extends Controller
{
    // Insumos con lotes que caducan dentro de N días (default 30)
    public function caducidad(Request $r){
        $dias = (int)($r->query('dias', 30));
        $hasta = now()->addDays($dias)->toDateString();

        $rows = DB::table('insumo_lotes as il')
            ->join('insumos as i','i.id','=','il.insumo_id')
            ->leftJoin('categorias_insumo as c','c.id','=','i.categoria_id')
            ->join('laboratorios as l','l.id','=','il.laboratorio_id')
            ->whereNotNull('il.caducidad')
            ->where('il.caducidad','<=',$hasta)
            ->where('il.cantidad','>',0)
            ->select([
                'i.codigo','i.nombre as insumo','c.nombre as categoria',
                'l.nombre as laboratorio','il.lote','il.caducidad','il.cantidad','i.unidad'
            ])
            ->orderBy('il.caducidad')
            ->get();

        return $rows;
    }

    // Insumos con stock total < stock_minimo
    public function bajoMinimo(Request $r){
        $stock = DB::table('insumo_lotes')
            ->select('insumo_id', DB::raw('SUM(cantidad) as stock'))
            ->groupBy('insumo_id');

        $rows = DB::table('insumos as i')
            ->leftJoinSub($stock, 'st', fn($j)=>$j->on('st.insumo_id','=','i.id'))
            ->leftJoin('categorias_insumo as c','c.id','=','i.categoria_id')
            ->select('i.id','i.codigo','i.nombre','i.unidad','i.stock_minimo',
                     DB::raw('COALESCE(st.stock,0) as stock'), 'c.nombre as categoria')
            ->whereRaw('COALESCE(st.stock,0) < i.stock_minimo')
            ->orderBy('i.nombre')
            ->get();

        return $rows;
    }
}
