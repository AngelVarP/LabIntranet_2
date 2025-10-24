<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class DelegadoController extends Controller
{
    private function mustAdminOrProfesor(Request $r){
        $u = $r->user();
        if (!$u || !$u->hasAnyRole(['admin','profesor'])) abort(403);
        return $u->id;
    }

    public function asignar(Request $r, int $grupoId){
        $uid = $this->mustAdminOrProfesor($r);
        $data = $r->validate(['alumno_id'=>'required|integer|exists:users,id']);

        DB::transaction(function() use($grupoId,$data,$uid){
            // cerrar delegado actual (si existe)
            $curr = DB::table('grupos')->where('id',$grupoId)->value('delegado_usuario_id');
            if ($curr) {
                DB::table('delegado_historial')
                  ->where('grupo_id',$grupoId)
                  ->whereNull('revocado_at')
                  ->update(['revocado_at'=>now()]);
            }
            // set nuevo
            DB::table('grupos')->where('id',$grupoId)->update(['delegado_usuario_id'=>$data['alumno_id']]);
            DB::table('delegado_historial')->insert([
                'grupo_id'=>$grupoId,'alumno_id'=>$data['alumno_id'],
                'asignado_por'=>$uid,'asignado_at'=>now()
            ]);
        });
        return ['ok'=>true];
    }

    public function revocar(Request $r, int $grupoId){
        $this->mustAdminOrProfesor($r);
        DB::table('delegado_historial')
            ->where('grupo_id',$grupoId)
            ->whereNull('revocado_at')
            ->update(['revocado_at'=>now()]);
        DB::table('grupos')->where('id',$grupoId)->update(['delegado_usuario_id'=>null]);
        return ['ok'=>true];
    }
}
