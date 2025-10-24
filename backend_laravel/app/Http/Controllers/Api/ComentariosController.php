<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class ComentariosController extends Controller
{
    public function index(Request $r,int $id){
        return DB::table('comentarios as c')
            ->join('users as u','u.id','=','c.autor_id')
            ->where('c.solicitud_id',$id)
            ->orderBy('c.creado_at')
            ->select('c.id','c.texto','c.creado_at','u.name as autor')
            ->get();
    }
    public function store(Request $r,int $id){
        $r->validate(['texto'=>'required|string|max:1000']);
        $uid = $r->user()?->id ?? abort(401);
        DB::table('comentarios')->insert([
            'solicitud_id'=>$id,'autor_id'=>$uid,'texto'=>$r->input('texto'),'creado_at'=>now()
        ]);
        return ['ok'=>true];
    }
}
