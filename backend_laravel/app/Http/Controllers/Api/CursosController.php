<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class CursosController extends Controller
{
    private function mustAdminOrProfesor(Request $r){
        $u = $r->user(); if(!$u || !$u->hasAnyRole(['admin','profesor'])) abort(403);
        return $u->id;
    }

    public function index(Request $r){
        $q = trim((string)$r->query('q',''));
        $qry = DB::table('cursos')
            ->when($q !== '', function($qq) use($q){
                $like = '%'.$q.'%';
                $qq->where(function($w) use($like){
                    $w->where('codigo','like',$like)->orWhere('nombre','like',$like)->orWhere('periodo','like',$like);
                });
            })
            ->orderBy('periodo','desc')->orderBy('nombre');
        $per = max(1, min((int)$r->query('per_page', 20), 100));
        return $qry->paginate($per);
    }

    public function show(Request $r, int $id){
        $row = DB::table('cursos')->where('id',$id)->first();
        if(!$row) abort(404);
        return $row;
    }

    public function store(Request $r){
        $this->mustAdminOrProfesor($r);
        $data = $r->validate([
            'codigo'=>['required','string','max:40'],
            'nombre'=>['required','string','max:140'],
            'periodo'=>['required','string','max:40'],
        ]);
        // idempotente por codigo
        $id = DB::table('cursos')->updateOrInsert(
            ['codigo'=>$data['codigo']],
            ['nombre'=>$data['nombre'],'periodo'=>$data['periodo'],'creado_at'=>now()]
        );
        // devolver el id real
        $cid = DB::table('cursos')->where('codigo',$data['codigo'])->value('id');
        return response()->json(['ok'=>true,'id'=>$cid], 201);
    }

    public function update(Request $r, int $id){
        $this->mustAdminOrProfesor($r);
        $data = $r->validate([
            'codigo'=>['required','string','max:40'],
            'nombre'=>['required','string','max:140'],
            'periodo'=>['required','string','max:40'],
        ]);
        $ok = DB::table('cursos')->where('id',$id)->update($data);
        if(!$ok) abort(404);
        return ['ok'=>true];
    }

    public function destroy(Request $r, int $id){
        $this->mustAdminOrProfesor($r);
        $enUso = DB::table('secciones')->where('curso_id',$id)->exists();
        if ($enUso) return response()->json(['error'=>'No se puede eliminar: el curso tiene secciones'], 422);

        $ok = DB::table('cursos')->where('id',$id)->delete();
        if(!$ok) abort(404);
        return ['ok'=>true];
    }

}
