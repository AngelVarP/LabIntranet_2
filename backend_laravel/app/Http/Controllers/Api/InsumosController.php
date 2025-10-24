<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InsumosController extends Controller
{
    private function assertTechOrAdmin(Request $request)
    {
        $u = $request->user();
        if (!$u || !$u->hasAnyRole(['admin','tecnico'])) {
            abort(403, 'No autorizado');
        }
    }

    public function index(Request $request)
    {
        // admin/tecnico ven todo; alumnos solo lectura (dejas tu lógica)
        $q     = trim((string)$request->query('q', ''));
        $catId = $request->query('categoria_id');
        $per   = (int)max(1, min((int)$request->query('per_page', 10), 100));

        // subconsulta para stock total desde insumo_lotes
        $stockSub = DB::table('insumo_lotes')
            ->select('insumo_id', DB::raw('SUM(cantidad) as stock_total'))
            ->groupBy('insumo_id');

        $qry = DB::table('insumos as i')
            ->leftJoin('categorias_insumo as c','c.id','=','i.categoria_id')
            ->leftJoinSub($stockSub, 'st', function($join){
                $join->on('st.insumo_id','=','i.id');
            })
            ->select([
                'i.id','i.nombre','i.codigo','i.unidad',
                'i.categoria_id',
                DB::raw('COALESCE(st.stock_total,0) as stock'),
                'i.stock_minimo as minimo',
                'i.activo',
                DB::raw('COALESCE(c.nombre,"") as categoria')
            ])
            ->when($q !== '', function ($qq) use ($q) {
                $like = '%'.$q.'%';
                $qq->where(function($w) use ($like) {
                    $w->where('i.nombre','like',$like)
                      ->orWhere('i.codigo','like',$like);
                });
            })
            ->when($catId, fn($qq) => $qq->where('i.categoria_id',$catId))
            ->orderBy('i.nombre');

        return $qry->paginate($per);
    }

    public function show(Request $request, int $id)
    {
        $ins = DB::table('insumos as i')
            ->leftJoin('categorias_insumo as c','c.id','=','i.categoria_id')
            ->leftJoin(DB::raw('(SELECT insumo_id, SUM(cantidad) stock_total FROM insumo_lotes GROUP BY insumo_id) st'),
                       'st.insumo_id','=','i.id')
            ->select('i.*',
                     DB::raw('COALESCE(st.stock_total,0) as stock'),
                     DB::raw('COALESCE(c.nombre,"") as categoria'))
            ->where('i.id',$id)->first();

        if (!$ins) abort(404);
        return $ins;
    }

    public function store(Request $request)
    {
        $this->assertTechOrAdmin($request);

        $data = $request->validate([
            'nombre'       => ['required','string','max:150'],
            'codigo'       => ['required','string','max:50'], // en tu esquema es NOT NULL UNIQUE
            'unidad'       => ['nullable','string','max:20'],
            'minimo'       => ['nullable','numeric','min:0'], // mapea a stock_minimo
            'categoria_id' => ['nullable','integer','exists:categorias_insumo,id'],
            'activo'       => ['nullable','boolean'],
        ]);

        $id = DB::table('insumos')->insertGetId([
            'nombre'       => $data['nombre'],
            'codigo'       => $data['codigo'],
            'unidad'       => $data['unidad'] ?? null,
            'stock_minimo' => $data['minimo'] ?? 0,
            'categoria_id' => $data['categoria_id'] ?? null,
            'activo'       => array_key_exists('activo',$data) ? (int)$data['activo'] : 1,
            // OJO: esta tabla no tiene created_at/updated_at
        ]);

        return response()->json(['ok'=>true,'id'=>$id], 201);
    }

    public function update(Request $request, int $id)
    {
        $this->assertTechOrAdmin($request);

        $data = $request->validate([
            'nombre'       => ['required','string','max:150'],
            'codigo'       => ['required','string','max:50'],
            'unidad'       => ['nullable','string','max:20'],
            'minimo'       => ['nullable','numeric','min:0'], // mapea a stock_minimo
            'categoria_id' => ['nullable','integer','exists:categorias_insumo,id'],
            'activo'       => ['nullable','boolean'],
        ]);

        $ok = DB::table('insumos')->where('id',$id)->update([
            'nombre'       => $data['nombre'],
            'codigo'       => $data['codigo'],
            'unidad'       => $data['unidad'] ?? null,
            'stock_minimo' => $data['minimo'] ?? 0,
            'categoria_id' => $data['categoria_id'] ?? null,
            'activo'       => array_key_exists('activo',$data) ? (int)$data['activo'] : 1,
        ]);

        if (!$ok) abort(404);
        return ['ok'=>true];
    }

    public function destroy(Request $request, int $id)
    {
        $this->assertTechOrAdmin($request);

        $ok = DB::table('insumos')->where('id',$id)->delete();
        if (!$ok) abort(404);
        return ['ok'=>true];
    }
    public function detalle(Request $r, int $id)
    {
        // Cabecera del insumo (con categoría)
        $ins = DB::table('insumos as i')
            ->leftJoin('categorias_insumo as c','c.id','=','i.categoria_id')
            ->where('i.id',$id)
            ->select(
                'i.id','i.codigo','i.nombre','i.unidad',
                'i.stock','i.minimo','i.activo',
                'i.categoria_id','c.nombre as categoria'
            )
            ->first();
        if(!$ins) abort(404, 'Insumo no encontrado');

        // Stock consolidado (sumando lotes)
        $stockConsolidado = (float) (DB::table('insumo_lotes')
            ->where('insumo_id',$id)
            ->sum('cantidad') ?? 0);

        // Lotes (orden: caducidad cercana primero; NULL al final)
        $lotes = DB::table('insumo_lotes as il')
            ->leftJoin('laboratorios as l','l.id','=','il.laboratorio_id')
            ->where('il.insumo_id',$id)
            ->orderByRaw('CASE WHEN il.caducidad IS NULL THEN 1 ELSE 0 END, il.caducidad ASC')
            ->get([
                'il.id','il.lote','il.caducidad','il.cantidad',
                'il.laboratorio_id','l.nombre as laboratorio'
            ]);

        // Últimos movimientos de kardex (solo INSUMO), top 20
        $kardex = DB::table('kardex_movimientos as k')
            ->leftJoin('users as u','u.id','=','k.usuario_id')
            ->leftJoin('laboratorios as l','l.id','=','k.laboratorio_id')
            ->where('k.tipo_item','INSUMO')
            ->where('k.item_id',$id)
            ->orderByDesc('k.fecha')
            ->limit(20)
            ->get([
                'k.id','k.fecha','k.tipo_mov','k.cantidad','k.motivo','k.referencia',
                'l.nombre as laboratorio','u.name as usuario'
            ]);

        // Flag de bajo mínimo (usa tus columnas existentes: stock/minimo)
        $bajoMinimo = $stockConsolidado < (float)($ins->minimo ?? 0);

        return [
            'insumo'            => $ins,
            'stock_consolidado' => $stockConsolidado,
            'bajo_minimo'       => $bajoMinimo,
            'lotes'             => $lotes,
            'kardex_reciente'   => $kardex,
        ];
    }

}
