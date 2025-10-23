<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoriasController extends Controller
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
        $q = trim((string)$request->query('q', ''));
        $per = (int)max(1, min((int)$request->query('per_page', 20), 100));

        $qry = DB::table('categorias')
            ->select('id','nombre','descripcion','activo')
            ->when($q !== '', fn($qq) =>
                $qq->where('nombre','like','%'.$q.'%')
                   ->orWhere('descripcion','like','%'.$q.'%')
            )
            ->orderBy('nombre');

        // si quieres sin paginar, retorna ->get(); yo dejo paginación
        return $qry->paginate($per);
    }

    public function store(Request $request)
    {
        $this->assertTechOrAdmin($request);

        $data = $request->validate([
            'nombre'      => ['required','string','max:120'],
            'descripcion' => ['nullable','string','max:255'],
            'activo'      => ['nullable','boolean'],
        ]);

        $id = DB::table('categorias')->insertGetId([
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
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
            'nombre'      => ['required','string','max:120'],
            'descripcion' => ['nullable','string','max:255'],
            'activo'      => ['nullable','boolean'],
        ]);

        $ok = DB::table('categorias')->where('id',$id)->update([
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'activo' => array_key_exists('activo',$data) ? (int)$data['activo'] : 1,
            'updated_at' => now(),
        ]);

        if (!$ok) abort(404);
        return ['ok'=>true];
    }

    public function destroy(Request $request, int $id)
    {
        $this->assertTechOrAdmin($request);

        // si hay insumos con esa categoría, podrías impedir o ponerla null
        $tieneInsumos = DB::table('insumos')->where('categoria_id',$id)->exists();
        if ($tieneInsumos) {
            abort(422, 'No se puede eliminar: hay insumos asociados (reasigna o elimina primero).');
        }

        $ok = DB::table('categorias')->where('id',$id)->delete();
        if (!$ok) abort(404);
        return ['ok'=>true];
    }
}
