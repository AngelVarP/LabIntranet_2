<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class SeccionesController extends Controller
{
    private function mustAdminOrProfesor(Request $r){
        $u = $r->user(); if(!$u || !$u->hasAnyRole(['admin','profesor'])) abort(403);
        return $u->id;
    }

    public function index(Request $r){
        $qry = DB::table('secciones as s')
            ->join('cursos as c','c.id','=','s.curso_id')
            ->select('s.id','s.nombre','s.curso_id','c.nombre as curso','c.codigo','c.periodo')
            ->when($r->filled('curso_id'), fn($q)=>$q->where('s.curso_id',$r->curso_id))
            ->orderBy('c.periodo','desc')->orderBy('c.nombre')->orderBy('s.nombre');
        $per = max(1, min((int)$r->query('per_page', 20), 100));
        return $qry->paginate($per);
    }

    public function show(Request $r, int $id){
        $row = DB::table('secciones as s')
            ->join('cursos as c','c.id','=','s.curso_id')
            ->select('s.*','c.nombre as curso','c.codigo','c.periodo')
            ->where('s.id',$id)->first();
        if(!$row) abort(404);
        return $row;
    }

    public function store(Request $r){
        $this->mustAdminOrProfesor($r);
        $data = $r->validate([
            'curso_id'=>['required','integer','exists:cursos,id'],
            'nombre'=>['required','string','max:40'],
        ]);
        // respeta unique(curso_id,nombre)
        DB::table('secciones')->updateOrInsert(
            ['curso_id'=>$data['curso_id'], 'nombre'=>$data['nombre']],
            []
        );
        $id = DB::table('secciones')->where($data)->value('id');
        return response()->json(['ok'=>true,'id'=>$id], 201);
    }

    public function update(Request $r, int $id){
        $this->mustAdminOrProfesor($r);
        $data = $r->validate([
            'curso_id'=>['required','integer','exists:cursos,id'],
            'nombre'=>['required','string','max:40'],
        ]);
        $ok = DB::table('secciones')->where('id',$id)->update($data);
        if(!$ok) abort(404);
        return ['ok'=>true];
    }

    public function destroy(Request $r, int $id){
        $this->mustAdminOrProfesor($r);
        $dep = DB::table('grupos')->where('seccion_id',$id)->exists()
            || DB::table('practicas')->where('seccion_id',$id)->exists();
        if($dep) return response()->json(['error'=>'No se puede eliminar: hay grupos/prácticas en la sección'], 422);

        $ok = DB::table('secciones')->where('id',$id)->delete();
        if(!$ok) abort(404);
        return ['ok'=>true];
    }


    // --- Profesores asignados a una sección ---

    public function profesores(Request $r, int $id){
        // profesores vigentes (hasta IS NULL o >= hoy)
        $hoy = now()->toDateString();
        return DB::table('profesor_seccion as ps')
            ->join('users as u','u.id','=','ps.profesor_id')
            ->where('ps.seccion_id',$id)
            ->where(function($q) use($hoy){
                $q->whereNull('ps.hasta')->orWhere('ps.hasta','>=',$hoy);
            })
            ->orderBy('ps.desde','desc')
            ->select('u.id as profesor_id','u.name','u.email','ps.desde','ps.hasta')
            ->get();
    }

    // body: { profesor_id, desde?=hoy }
    public function asignarProfesor(Request $r, int $id){
        $this->mustAdminOrProfesor($r);
        $data = $r->validate([
            'profesor_id'=>['required','integer','exists:users,id'],
            'desde'=>['nullable','date']
        ]);
        $desde = $data['desde'] ?? now()->toDateString();

        DB::transaction(function() use($id,$data,$desde){
            // cerrar vigentes del mismo profesor en la misma sección si hubiera superposición
            DB::table('profesor_seccion')
                ->where('seccion_id',$id)->where('profesor_id',$data['profesor_id'])
                ->whereNull('hasta')->update(['hasta'=>$desde]);

            DB::table('profesor_seccion')->insert([
                'seccion_id'=>$id,'profesor_id'=>$data['profesor_id'],
                'desde'=>$desde,'hasta'=>null
            ]);
        });

        return ['ok'=>true];
    }

    // body: { profesor_id, hasta?=hoy }
    public function revocarProfesor(Request $r, int $id){
        $this->mustAdminOrProfesor($r);
        $data = $r->validate([
            'profesor_id'=>['required','integer','exists:users,id'],
            'hasta'=>['nullable','date']
        ]);
        $hasta = $data['hasta'] ?? now()->toDateString();

        $ok = DB::table('profesor_seccion')
            ->where('seccion_id',$id)->where('profesor_id',$data['profesor_id'])
            ->whereNull('hasta')->update(['hasta'=>$hasta]);
        if(!$ok) abort(404);

        return ['ok'=>true];
    }
}
