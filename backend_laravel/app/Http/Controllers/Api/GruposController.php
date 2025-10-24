<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class GruposController extends Controller
{
    private function mustAdminOrProfesor(Request $r){
        $u = $r->user(); if(!$u || !$u->hasAnyRole(['admin','profesor'])) abort(403);
        return $u->id;
    }

    public function index(Request $r){
        $qry = DB::table('grupos as g')
            ->join('secciones as s','s.id','=','g.seccion_id')
            ->join('cursos as c','c.id','=','s.curso_id')
            ->leftJoin('users as u','u.id','=','g.delegado_usuario_id')
            ->when($r->filled('seccion_id'), fn($q)=>$q->where('g.seccion_id',$r->seccion_id))
            ->select('g.id','g.nombre','g.seccion_id','s.nombre as seccion','c.nombre as curso',
                     'g.delegado_usuario_id','u.name as delegado')
            ->orderBy('c.nombre')->orderBy('s.nombre')->orderBy('g.nombre');

        $per = max(1, min((int)$r->query('per_page', 20), 100));
        return $qry->paginate($per);
    }

    public function show(Request $r, int $id){
        $row = DB::table('grupos')->where('id',$id)->first();
        if(!$row) abort(404);
        return $row;
    }

    public function store(Request $r){
        $this->mustAdminOrProfesor($r);
        $data = $r->validate([
            'seccion_id'=>['required','integer','exists:secciones,id'],
            'nombre'=>['required','string','max:50'],
        ]);
        DB::table('grupos')->updateOrInsert(
            ['seccion_id'=>$data['seccion_id'],'nombre'=>$data['nombre']],
            ['delegado_usuario_id'=>null]
        );
        $id = DB::table('grupos')->where($data)->value('id');
        return response()->json(['ok'=>true,'id'=>$id], 201);
    }

    public function update(Request $r, int $id){
        $this->mustAdminOrProfesor($r);
        $data = $r->validate([
            'seccion_id'=>['required','integer','exists:secciones,id'],
            'nombre'=>['required','string','max:50'],
        ]);
        $ok = DB::table('grupos')->where('id',$id)->update($data);
        if(!$ok) abort(404);
        return ['ok'=>true];
    }

    public function destroy(Request $r, int $id){
        $this->mustAdminOrProfesor($r);
        $dep = DB::table('alumnos_grupo')->where('grupo_id',$id)->exists()
            || DB::table('solicitudes')->where('grupo_id',$id)->exists();
        if($dep) return response()->json(['error'=>'No se puede eliminar: hay alumnos/solicitudes del grupo'], 422);

        $ok = DB::table('grupos')->where('id',$id)->delete();
        if(!$ok) abort(404);
        return ['ok'=>true];
    }


    // --- miembros (alumnos) ---

    public function alumnos(Request $r, int $id){
        return DB::table('alumnos_grupo as ag')
            ->join('users as u','u.id','=','ag.alumno_id')
            ->where('ag.grupo_id',$id)
            ->orderBy('u.name')
            ->select('u.id as alumno_id','u.name','u.email')
            ->get();
    }

    // body: { alumno_id }
    public function agregarAlumno(Request $r, int $id){
        $this->mustAdminOrProfesor($r);
        $data = $r->validate(['alumno_id'=>['required','integer','exists:users,id']]);
        DB::table('alumnos_grupo')->updateOrInsert(
            ['grupo_id'=>$id,'alumno_id'=>$data['alumno_id']],
            []
        );
        return ['ok'=>true];
    }

    // body: { alumno_id }
    public function quitarAlumno(Request $r, int $id){
        $this->mustAdminOrProfesor($r);
        $data = $r->validate(['alumno_id'=>['required','integer','exists:users,id']]);
        $ok = DB::table('alumnos_grupo')->where(['grupo_id'=>$id,'alumno_id'=>$data['alumno_id']])->delete();
        if(!$ok) abort(404);
        return ['ok'=>true];
    }
}
