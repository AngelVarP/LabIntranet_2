<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class PracticasController extends Controller
{
    private function mustAdminOrProfesor(Request $r){
        $u = $r->user();
        if (!$u || !$u->hasAnyRole(['admin','profesor'])) abort(403);
        return $u->id;
    }

    // filtros: ?seccion_id=&curso_id=&laboratorio_id=&q=
    public function index(Request $r){
        $qtxt = trim((string)$r->query('q',''));

        $q = DB::table('practicas as p')
            ->join('secciones as s','s.id','=','p.seccion_id')
            ->join('cursos as c','c.id','=','s.curso_id')
            ->leftJoin('laboratorios as l','l.id','=','p.laboratorio_id')
            ->leftJoin('laboratorios_turnos as t','t.id','=','p.turno_id')
            ->select([
                'p.id','p.seccion_id','p.titulo','p.descripcion','p.fecha',
                'p.laboratorio_id','p.turno_id','p.habilitada','p.creado_at',
                's.nombre as seccion','c.nombre as curso','l.nombre as laboratorio','t.nombre as turno'
            ])
            ->when($r->filled('seccion_id'), fn($qq)=>$qq->where('p.seccion_id', $r->seccion_id))
            ->when($r->filled('curso_id'),   fn($qq)=>$qq->where('c.id', $r->curso_id))
            ->when($r->filled('laboratorio_id'), fn($qq)=>$qq->where('p.laboratorio_id', $r->laboratorio_id))
            ->when($qtxt !== '', function($qq) use($qtxt){
                $like = '%'.$qtxt.'%';
                $qq->where(function($w) use($like){
                    $w->where('p.titulo','like',$like)
                      ->orWhere('c.nombre','like',$like)
                      ->orWhere('s.nombre','like',$like);
                });
            })
            ->orderByDesc('p.fecha')->orderBy('p.titulo');

        $per = max(1, min((int)$r->query('per_page', 15), 100));
        return $q->paginate($per);
    }

    public function show(Request $r, int $id){
        $row = DB::table('practicas as p')
            ->leftJoin('laboratorios as l','l.id','=','p.laboratorio_id')
            ->leftJoin('laboratorios_turnos as t','t.id','=','p.turno_id')
            ->join('secciones as s','s.id','=','p.seccion_id')
            ->join('cursos as c','c.id','=','s.curso_id')
            ->where('p.id',$id)
            ->select('p.*','l.nombre as laboratorio','t.nombre as turno','s.nombre as seccion','c.nombre as curso')
            ->first();
        if (!$row) abort(404);
        return $row;
    }

    public function store(Request $r){
        $this->mustAdminOrProfesor($r);
        $data = $r->validate([
            'seccion_id'    => ['required','integer','exists:secciones,id'],
            'titulo'        => ['required','string','max:180'],
            'descripcion'   => ['nullable','string'],
            'fecha'         => ['required','date'],
            'laboratorio_id'=> ['required','integer','exists:laboratorios,id'],
            'turno_id'      => ['nullable','integer','exists:laboratorios_turnos,id'],
            'habilitada'    => ['nullable','boolean']
        ]);

        $id = DB::table('practicas')->insertGetId([
            'seccion_id'=>$data['seccion_id'],
            'titulo'=>$data['titulo'],
            'descripcion'=>$data['descripcion'] ?? null,
            'fecha'=>$data['fecha'],
            'laboratorio_id'=>$data['laboratorio_id'],
            'turno_id'=>$data['turno_id'] ?? null,
            'habilitada'=> array_key_exists('habilitada',$data) ? (int)$data['habilitada'] : 1,
            'creado_at'=> now(),
        ]);

        return response()->json(['ok'=>true,'id'=>$id],201);
    }

    public function update(Request $r, int $id){
        $this->mustAdminOrProfesor($r);
        $data = $r->validate([
            'seccion_id'    => ['required','integer','exists:secciones,id'],
            'titulo'        => ['required','string','max:180'],
            'descripcion'   => ['nullable','string'],
            'fecha'         => ['required','date'],
            'laboratorio_id'=> ['required','integer','exists:laboratorios,id'],
            'turno_id'      => ['nullable','integer','exists:laboratorios_turnos,id'],
            'habilitada'    => ['nullable','boolean']
        ]);

        $ok = DB::table('practicas')->where('id',$id)->update([
            'seccion_id'=>$data['seccion_id'],
            'titulo'=>$data['titulo'],
            'descripcion'=>$data['descripcion'] ?? null,
            'fecha'=>$data['fecha'],
            'laboratorio_id'=>$data['laboratorio_id'],
            'turno_id'=>$data['turno_id'] ?? null,
            'habilitada'=> array_key_exists('habilitada',$data) ? (int)$data['habilitada'] : 1,
            // 'actualizado_at' no existe en esta tabla en tu esquema, por eso no lo seteo
        ]);
        if(!$ok) abort(404);
        return ['ok'=>true];
    }

    public function destroy(Request $r, int $id){
        $this->mustAdminOrProfesor($r);
        $ok = DB::table('practicas')->where('id',$id)->delete();
        if(!$ok) abort(404);
        return ['ok'=>true];
    }
}
