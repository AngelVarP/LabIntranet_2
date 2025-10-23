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
        // acceso: admin/tecnico ven todo; alumnos solo lectura (si quieres permitir, quita esta línea)
        if ($request->user()->hasAnyRole(['admin','tecnico']) === false) {
            // solo lectura (si prefieres bloquear totalmente: abort(403))
        }

        $q     = trim((string)$request->query('q', ''));
        $catId = $request->query('categoria_id');
        $per   = (int)max(1, min((int)$request->query('per_page', 10), 100));

        $qry = DB::table('insumos as i')
            ->leftJoin('categorias as c','c.id','=','i.categoria_id')
            ->select('i.id','i.nombre','i.codigo','i.unidad','i.stock','i.minimo','i.activo',
                     'i.categoria_id','c.nombre as categoria')
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
        $ins = DB::table('insumos')->where('id',$id)->first();
        if (!$ins) abort(404);
        return $ins;
    }

    public function store(Request $request)
    {
        $this->assertTechOrAdmin($request);

        $data = $request->validate([
            'nombre'       => ['required','string','max:150'],
            'codigo'       => ['nullable','string','max:50'],
            'unidad'       => ['nullable','string','max:20'],
            'stock'        => ['nullable','numeric','min:0'],
            'minimo'       => ['nullable','numeric','min:0'],
            'categoria_id' => ['nullable','integer','exists:categorias,id'],
            'activo'       => ['nullable','boolean'],
        ]);

        $id = DB::table('insumos')->insertGetId([
            'nombre' => $data['nombre'],
            'codigo' => $data['codigo'] ?? null,
            'unidad' => $data['unidad'] ?? null,
            'stock'  => $data['stock']  ?? 0,
            'minimo' => $data['minimo'] ?? 0,
            'categoria_id' => $data['categoria_id'] ?? null,
            'activo' => array_key_exists('activo',$data) ? (int)$data['activo'] : 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['ok'=>true,'id'=>$id], 201);
    }

    public function update(Request $request, int $id)
    {
        $this->assertTechOrAdmin($request);

        $data = $request->validate([
            'nombre'       => ['required','string','max:150'],
            'codigo'       => ['nullable','string','max:50'],
            'unidad'       => ['nullable','string','max:20'],
            'stock'        => ['nullable','numeric','min:0'],
            'minimo'       => ['nullable','numeric','min:0'],
            'categoria_id' => ['nullable','integer','exists:categorias,id'],
            'activo'       => ['nullable','boolean'],
        ]);

        $ok = DB::table('insumos')->where('id',$id)->update([
            'nombre' => $data['nombre'],
            'codigo' => $data['codigo'] ?? null,
            'unidad' => $data['unidad'] ?? null,
            'stock'  => $data['stock']  ?? 0,
            'minimo' => $data['minimo'] ?? 0,
            'categoria_id' => $data['categoria_id'] ?? null,
            'activo' => array_key_exists('activo',$data) ? (int)$data['activo'] : 1,
            'updated_at' => now(),
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
}
