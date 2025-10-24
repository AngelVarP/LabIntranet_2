<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoriasInsumoController extends Controller
{
    private function mustTechOrAdmin(Request $r){
        $u = $r->user(); if(!$u || !$u->hasAnyRole(['admin','tecnico'])) abort(403,'No autorizado');
    }

    // GET /api/categorias-insumo?q=&per_page=
    public function index(Request $r){
        $q   = trim((string)$r->query('q',''));
        $per = max(1, min((int)$r->query('per_page', 20), 100));
        $qry = DB::table('categorias_insumo')
            ->when($q !== '', function($qq) use($q){
                $like='%'.$q.'%';
                $qq->where('nombre','like',$like);
            })
            ->orderBy('nombre');
        return $qry->paginate($per, ['id','nombre']);
    }

    // POST /api/categorias-insumo {nombre}
    public function store(Request $r){
        $this->mustTechOrAdmin($r);
        $data = $r->validate(['nombre'=>['required','string','max:100']]);
        // único por nombre
        $exists = DB::table('categorias_insumo')->where('nombre',$data['nombre'])->exists();
        if($exists) return response()->json(['error'=>'Ya existe una categoría con ese nombre'], 422);
        $id = DB::table('categorias_insumo')->insertGetId(['nombre'=>$data['nombre']]);
        return response()->json(['ok'=>true,'id'=>$id], 201);
    }

    // PUT /api/categorias-insumo/{id} {nombre}
    public function update(Request $r, int $id){
        $this->mustTechOrAdmin($r);
        $data = $r->validate(['nombre'=>['required','string','max:100']]);
        $dup = DB::table('categorias_insumo')
            ->where('nombre',$data['nombre'])
            ->where('id','<>',$id)
            ->exists();
        if($dup) return response()->json(['error'=>'Nombre ya usado por otra categoría'], 422);

        $ok = DB::table('categorias_insumo')->where('id',$id)->update(['nombre'=>$data['nombre']]);
        if(!$ok) abort(404);
        return ['ok'=>true];
    }

    // DELETE /api/categorias-insumo/{id}
    public function destroy(Request $r, int $id){
        $this->mustTechOrAdmin($r);
        // evita borrar si está en uso por algún insumo
        $enUso = DB::table('insumos')->where('categoria_id',$id)->exists();
        if($enUso) return response()->json(['error'=>'No se puede eliminar: hay insumos usando esta categoría'], 422);

        $ok = DB::table('categorias_insumo')->where('id',$id)->delete();
        if(!$ok) abort(404);
        return ['ok'=>true];
    }
}
