<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SolicitudAdjuntosController extends Controller
{
    // Permite admin/tecnico/profesor o el delegado dueño de la solicitud
    private function canEdit(Request $r, int $solicitudId): bool {
        $u = $r->user(); if(!$u) return false;
        if ($u->hasAnyRole(['admin','tecnico','profesor'])) return true;
        $delegado = DB::table('solicitudes')->where('id',$solicitudId)->value('delegado_id');
        return $delegado && ((int)$delegado === (int)$u->id);
    }

    public function listar(Request $r, int $id){
        // lectura para cualquiera autenticado que tenga acceso al detalle (mantenemos simple)
        $rows = DB::table('solicitud_adjuntos')
            ->where('solicitud_id',$id)
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
            'file' => ['required','file','max:10240'] // 10MB; ajusta si necesitas
        ]);

        $file = $r->file('file');
        $orig = $file->getClientOriginalName();
        $mime = $file->getClientMimeType();

        // Guarda en storage/app/public/adjuntos/solicitudes/{id}/...
        $path = $file->store("adjuntos/solicitudes/{$id}", 'public');
        $url  = Storage::disk('public')->url($path);

        DB::table('solicitud_adjuntos')->insert([
            'solicitud_id' => $id,
            'nombre_archivo' => $orig,
            'tipo_mime' => $mime,
            'url' => $url,
            'subido_por' => $r->user()->id,
            'subido_at' => now(),
        ]);

        return response()->json(['ok'=>true,'url'=>$url], 201);
    }

    public function eliminar(Request $r, int $aid){
        // Busca el adjunto y verifica permiso sobre su solicitud
        $adj = DB::table('solicitud_adjuntos')->where('id',$aid)->first();
        if(!$adj) abort(404);

        if (!$this->canEdit($r, (int)$adj->solicitud_id)) abort(403,'No autorizado');

        // Borra archivo físico si está en nuestro storage público
        // (solo si comienza con /storage/adjuntos/... para no eliminar URLs externas)
        if (is_string($adj->url) && str_starts_with($adj->url, '/storage/')) {
            // url => /storage/adjuntos/solicitudes/{id}/xxx
            $rel = substr($adj->url, strlen('/storage/')); // adjuntos/...
            Storage::disk('public')->delete($rel);
        }

        DB::table('solicitud_adjuntos')->where('id',$aid)->delete();
        return ['ok'=>true];
    }
}
