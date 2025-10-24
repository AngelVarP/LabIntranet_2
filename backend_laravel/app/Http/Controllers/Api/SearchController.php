<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class SearchController extends Controller
{
    

    public function equipos(Request $r){
        $q = trim((string)$r->query('q',''));
        if($q==='') return [];
        $like = '%'.$q.'%';
        return DB::table('equipos')
            ->where(function($w) use($like){
                $w->where('nombre','like',$like)->orWhere('codigo','like',$like)->orWhere('modelo','like',$like);
            })
            ->orderBy('nombre')
            ->limit(20)
            ->get(['id','codigo','nombre','modelo','estado','laboratorio_id']);
    }

    // ?q= &role=alumno|profesor|tecnico|admin (opcional)
    public function users(Request $r){
        $q = trim((string)$r->query('q',''));
        $role = $r->query('role');
        if($q==='') return [];

        $like = '%'.$q.'%';
        $users = DB::table('users as u')
            ->when($role, function($qq) use($role){
                // filtra por rol usando spatie tables
                $qq->join('model_has_roles as mr','mr.model_id','=','u.id')
                   ->join('roles as r','r.id','=','mr.role_id')
                   ->where('r.name',$role)
                   ->where('mr.model_type','App\\Models\\User');
            })
            ->where(function($w) use($like){
                $w->where('u.name','like',$like)->orWhere('u.email','like',$like);
            })
            ->orderBy('u.name')
            ->limit(20)
            ->get(['u.id','u.name','u.email']);

        return $users;
    }
    public function laboratorios(Request $r){
        $limit = max(1, min((int)$r->query('limit', 50), 200));
        return DB::table('laboratorios')
            ->orderBy('nombre')
            ->limit($limit)
            ->get(['id','nombre']);
    }

    public function cursos(Request $r){
        $limit = max(1, min((int)$r->query('limit', 100), 500));
        // puedes filtrar por periodo si luego lo necesitas: ?periodo=2025-I
        return DB::table('cursos')
            ->when($r->filled('periodo'), fn($q)=>$q->where('periodo',$r->periodo))
            ->orderBy('nombre')
            ->limit($limit)
            ->get(['id','nombre','periodo']);
    }

    public function secciones(Request $r){
        $r->validate(['curso_id'=>'required|integer']);
        return DB::table('secciones')
            ->where('curso_id',$r->curso_id)
            ->orderBy('nombre')
            ->get(['id','nombre','curso_id']);
    }

    public function grupos(Request $r){
        $r->validate(['seccion_id'=>'required|integer']);
        return DB::table('grupos')
            ->where('seccion_id',$r->seccion_id)
            ->orderBy('nombre')
            ->get(['id','nombre','seccion_id']);
    }

    public function categoriasInsumo(Request $r){
        $limit = max(1, min((int)$r->query('limit', 100), 500));
        return DB::table('categorias_insumo')
            ->orderBy('nombre')
            ->limit($limit)
            ->get(['id','nombre']);
    }

    public function insumosBuscar(Request $r){
        $q     = trim((string)$r->query('q',''));
        $limit = max(1, min((int)$r->query('limit', 20), 100));
        return DB::table('insumos')
            ->when($q !== '', function($qq) use($q){
                $like = '%'.$q.'%';
                $qq->where(fn($w)=>$w->where('nombre','like',$like)->orWhere('codigo','like',$like));
            })
            ->orderBy('nombre')
            ->limit($limit)
            ->get(['id','nombre','codigo','unidad','categoria_id']);
    }
    public function insumos(Request $r){
        $q = trim((string)$r->query('q',''));
        if($q==='') return [];
        $like = '%'.$q.'%';
        return DB::table('insumos')
            ->where(function($w) use($like){
                $w->where('nombre','like',$like)->orWhere('codigo','like',$like);
            })
            ->orderBy('nombre')
            ->limit(20)
            ->get(['id','codigo','nombre','unidad','categoria_id']);
    }

    public function equiposBuscar(Request $r){
        $q     = trim((string)$r->query('q',''));
        $limit = max(1, min((int)$r->query('limit', 20), 100));
        return DB::table('equipos')
            ->when($q !== '', function($qq) use($q){
                $like = '%'.$q.'%';
                $qq->where(fn($w)=>$w->where('nombre','like',$like)->orWhere('codigo','like',$like)->orWhere('modelo','like',$like));
            })
            ->orderBy('nombre')
            ->limit($limit)
            ->get(['id','nombre','codigo','modelo','estado','laboratorio_id']);
    }
}
