<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DevolucionesAdjuntosController extends Controller
{
    private function mustAnyLogged(Request $r){ if(!$r->user()) abort(401); }
    private function mustTecOrAdmin(Request $r){
        $u = $r->user(); if(!$u || !$u->hasAnyRole(['tecnico','admin'])) abort(403,'No autorizado'); return $u->id;
    }

    // GET /api/devoluciones/{id}/adjuntos
    public function index(Request $r, int $id){
        $this->mustAnyLogged($r);
        // verifica que exista la devolución
        $exists = DB::table('devoluciones')->where('id',$id)->exists();
        if(!$exists) abort(404);

        return DB::table('archivos_adjuntos')
            ->where(['entidad'=>'DEVOLUCION','entidad_id'=>$id])
            ->orderByDesc('subido_at')
            ->get(['id','nombre_archivo','tipo_mime','url','subido_por','subido_at']);
    }

    // POST /api/devoluciones/{id}/adjuntos  body: {nombre_archivo, url, tipo_mime?}
    public function store(Request $r, int $id){
        $uid = $this->mustTecOrAdmin($r);

        // verifica devolución
        $exists = DB::table('devoluciones')->where('id',$id)->exists();
        if(!$exists) abort(404,'Devolución no encontrada');

        $data = $r->validate([
            'nombre_archivo'=>['required','string','max:200'],
            'url'           =>['required','string','max:400'], // URL ya subida (S3/local)
            'tipo_mime'     =>['nullable','string','max:120'],
        ]);

        $adjId = DB::table('archivos_adjuntos')->insertGetId([
            'entidad'       =>'DEVOLUCION',
            'entidad_id'    =>$id,
            'nombre_archivo'=>$data['nombre_archivo'],
            'tipo_mime'     =>$data['tipo_mime'] ?? null,
            'url'           =>$data['url'],
            'subido_por'    =>$uid,
            'subido_at'     =>now(),
        ]);

        return response()->json(['ok'=>true,'id'=>$adjId], 201);
    }

    // DELETE /api/devoluciones/adjuntos/{adjuntoId}
    public function destroy(Request $r, int $adjuntoId){
        $this->mustTecOrAdmin($r);

        $row = DB::table('archivos_adjuntos')->where('id',$adjuntoId)->first(['id','entidad','entidad_id']);
        if(!$row || $row->entidad!=='DEVOLUCION') abort(404);

        DB::table('archivos_adjuntos')->where('id',$adjuntoId)->delete();
        return ['ok'=>true];
    }
}
