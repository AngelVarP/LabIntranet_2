<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventarioController extends Controller
{
    // --- Helpers ---
    private function mustTechOrAdmin(Request $r){
        $u = $r->user(); if(!$u || !$u->hasAnyRole(['admin','tecnico'])) abort(403,'No autorizado');
        return $u;
    }

    // 1) STOCK por INSUMO (total y por laboratorio, con próximas caducidades)
    public function stockPorInsumo(Request $r, int $id){
        // opcional: filtrar por laboratorio_id
        $labId = $r->query('laboratorio_id');

        // Totales por laboratorio
        $porLab = DB::table('insumo_lotes as il')
            ->join('laboratorios as l','l.id','=','il.laboratorio_id')
            ->where('il.insumo_id',$id)
            ->when($labId, fn($q)=>$q->where('il.laboratorio_id',$labId))
            ->groupBy('il.laboratorio_id','l.nombre')
            ->orderBy('l.nombre')
            ->get([
                'il.laboratorio_id',
                'l.nombre as laboratorio',
                DB::raw('SUM(il.cantidad) as stock_total'),
                DB::raw('SUM(CASE WHEN il.caducidad IS NOT NULL AND il.caducidad>=CURDATE() THEN il.cantidad ELSE 0 END) as stock_no_vencido'),
            ]);

        // Próximas caducidades (primeros 5 lotes no vencidos)
        $cad = DB::table('insumo_lotes')
            ->where('insumo_id',$id)
            ->when($labId, fn($q)=>$q->where('laboratorio_id',$labId))
            ->whereNotNull('caducidad')
            ->where('caducidad','>=',now()->toDateString())
            ->orderBy('caducidad')
            ->limit(5)
            ->get(['lote','caducidad','cantidad','laboratorio_id']);

        // Meta del insumo (mínimo/unidad)
        $meta = DB::table('insumos')->where('id',$id)->first(['id','nombre','codigo','unidad','stock_minimo']);
        if(!$meta) abort(404,'Insumo no encontrado');

        // Total consolidado
        $total = (float) DB::table('insumo_lotes')->where('insumo_id',$id)
            ->when($labId, fn($q)=>$q->where('laboratorio_id',$labId))
            ->sum('cantidad');

        return [
            'insumo' => $meta,
            'total'  => $total,
            'por_laboratorio' => $porLab,
            'proximas_caducidades' => $cad,
            'bajo_minimo' => $meta->stock_minimo !== null ? ($total < (float)$meta->stock_minimo) : false,
        ];
    }

    // 2) KARDEX del INSUMO (paginado light)
    public function kardexPorInsumo(Request $r, int $id){
        $r->validate([
            'laboratorio_id' => 'nullable|integer',
            'desde'          => 'nullable|date',
            'hasta'          => 'nullable|date',
        ]);

        $per = max(10, min((int)$r->query('per_page', 50), 200));

        $q = DB::table('kardex_movimientos as k')
            ->leftJoin('laboratorios as l','l.id','=','k.laboratorio_id')
            ->where('k.tipo_item','INSUMO')
            ->where('k.item_id',$id)
            ->when($r->filled('laboratorio_id'), fn($qq)=>$qq->where('k.laboratorio_id',$r->laboratorio_id))
            ->when($r->filled('desde'), fn($qq)=>$qq->where('k.fecha','>=',$r->desde.' 00:00:00'))
            ->when($r->filled('hasta'), fn($qq)=>$qq->where('k.fecha','<=',$r->hasta.' 23:59:59'))
            ->orderByDesc('k.fecha');

        return $q->paginate($per, [
            'k.id','k.fecha','l.nombre as laboratorio','k.tipo_mov','k.cantidad','k.motivo','k.referencia','k.usuario_id'
        ]);
    }

    /**
     * 3) AJUSTE de stock (admin/técnico)
     * Body JSON:
     * {
     *   "laboratorio_id": 1,
     *   "cantidad": -2.5,            // negativo = egreso; positivo = ingreso
     *   "motivo": "AJUSTE_INVENTARIO",
     *   "lote": null,                // opcional
     *   "caducidad": null            // opcional YYYY-MM-DD
     * }
     * Efectos:
     * - Inserta un registro en kardex_movimientos (tipo_mov = AJUSTE)
     * - Refleja el ajuste creando un registro en insumo_lotes (cantidad positiva o negativa)
     */
    public function ajustarStock(Request $r, int $id){
        $u = $this->mustTechOrAdmin($r);

        $data = $r->validate([
            'laboratorio_id' => ['required','integer','exists:laboratorios,id'],
            'cantidad'       => ['required','numeric','not_in:0'],
            'motivo'         => ['nullable','string','max:200'],
            'lote'           => ['nullable','string','max:80'],
            'caducidad'      => ['nullable','date'],
        ]);

        DB::transaction(function() use($data,$id,$u){
            // 3.1 kardex (AJUSTE)
            DB::table('kardex_movimientos')->insert([
                'laboratorio_id' => $data['laboratorio_id'],
                'tipo_item'      => 'INSUMO',
                'item_id'        => $id,
                'tipo_mov'       => 'AJUSTE',
                'cantidad'       => $data['cantidad'],
                'motivo'         => $data['motivo'] ?? 'AJUSTE_MANUAL',
                'referencia'     => null,
                'fecha'          => now(),
                'usuario_id'     => $u->id,
            ]);

            // 3.2 reflejar en insumo_lotes (un registro simple del ajuste)
            DB::table('insumo_lotes')->insert([
                'insumo_id'     => $id,
                'lote'          => $data['lote'] ?? null,
                'caducidad'     => $data['caducidad'] ?? null,
                'cantidad'      => $data['cantidad'], // puede ser negativa
                'laboratorio_id'=> $data['laboratorio_id'],
            ]);
        });

        return ['ok'=>true];
    }
}
