<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class KardexController extends Controller
{
    public function index(Request $r){
        $r->validate([
            'tipo_item'=>'nullable|in:INSUMO,EQUIPO',
            'laboratorio_id'=>'nullable|integer',
            'item_id'=>'nullable|integer',
            'desde'=>'nullable|date',
            'hasta'=>'nullable|date'
        ]);

        $q = DB::table('kardex_movimientos')->orderByDesc('fecha');
        if($r->filled('tipo_item'))      $q->where('tipo_item',$r->tipo_item);
        if($r->filled('laboratorio_id')) $q->where('laboratorio_id',$r->laboratorio_id);
        if($r->filled('item_id'))        $q->where('item_id',$r->item_id);
        if($r->filled('desde'))          $q->where('fecha','>=', $r->desde.' 00:00:00');
        if($r->filled('hasta'))          $q->where('fecha','<=', $r->hasta.' 23:59:59');

        $per = max(1, min((int)$r->query('per_page', 50), 200));
        return $q->paginate($per);
    }
}
