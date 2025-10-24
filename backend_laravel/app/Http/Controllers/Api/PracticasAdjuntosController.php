<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PracticaAdjuntosController extends Controller
{
    // Permisos: admin/tecnico/profesor pueden editar; lectura libre para autenticados
    private function canEdit(Request $r, int $practicaId): bool {
        $u = $r->user(); if(!$u) return false;
        return $u->hasAnyRole(['admin','tecnico','profesor']);
    }

    public function listar(Request $r, int $id){
        $rows = DB::table('archivos_adjuntos')
            ->where('entidad','PRACTICA')
            ->where('entidad_id',$id)
            ->orderByDesc('subido_at')
            ->get(['id','nombre_archivo','tipo_mime','url','subido_por','subido_at']);
        return $rows;
    }

    /**
     * multipart/form-data:
     *   - file: (requerido) archivo a subir
     */
    public function subir(Request $r, int $id){
        if (!$this->canEdit($r,$id)) abort(403,'No autorizado');

        $r->validate([
            'file' => ['required','file','max:10240'] // 10MB
        ]);

        $file = $r->file('file');
        $orig = $file->getClientOriginalName();
        $mime = $file->getClientMimeType();

        // Guarda en storage/app/public/adjuntos/practicas/{id}/...
        $path = $file->store("adjuntos/practicas/{$id}", 'public');
        $url  = Storage::disk('public')->url($path);

        DB::table('archivos_adjuntos')->insert([
            'entidad'       => 'PRACTICA',
            'entidad_id'    => $id,
            'nombre_archivo'=> $orig,
            'tipo_mime'     => $mime,
            'url'           => $url,
            'subido_por'    => $r->user()->id,
            'subido_at'     => now(),
        ]);

        return response()->json(['ok'=>true,'url'=>$url], 201);
    }

    public function eliminar(Request $r, int $aid){
        $adj = DB::table('archivos_adjuntos')->where('id',$aid)->first();
        if(!$adj) abort(404);
        if ($adj->entidad !== 'PRACTICA') abort(400,'Adjunto no es de práctica');

        if (!$this->canEdit($r, (int)$adj->entidad_id)) abort(403,'No autorizado');

        // Borra archivo físico si es de nuestro storage público
        if (is_string($adj->url) && str_starts_with($adj->url, '/storage/')) {
            $rel = substr($adj->url, strlen('/storage/')); // adjuntos/...
            Storage::disk('public')->delete($rel);
        }

        DB::table('archivos_adjuntos')->where('id',$aid)->delete();
        return ['ok'=>true];
    }
}
