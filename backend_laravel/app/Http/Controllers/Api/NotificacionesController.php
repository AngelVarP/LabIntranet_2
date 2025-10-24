<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class NotificacionesController extends Controller
{
    public function index(Request $r){
        $uid = $r->user()?->id ?? abort(401);
        $soloNoLeidas = filter_var($r->query('no_leidas', 'false'), FILTER_VALIDATE_BOOLEAN);

        $q = DB::table('notificaciones')
            ->where('usuario_id',$uid)
            ->when($soloNoLeidas, fn($qq)=>$qq->where('leida',0))
            ->orderByDesc('creado_at');

        $per = max(1, min((int)$r->query('per_page', 20), 100));
        return $q->paginate($per);
    }

    public function marcarLeida(Request $r, int $id){
        $uid = $r->user()?->id ?? abort(401);
        $ok = DB::table('notificaciones')->where('id',$id)->where('usuario_id',$uid)->update(['leida'=>1]);
        if(!$ok) abort(404);
        return ['ok'=>true];
    }

    public function marcarTodas(Request $r){
        $uid = $r->user()?->id ?? abort(401);
        DB::table('notificaciones')->where('usuario_id',$uid)->update(['leida'=>1]);
        return ['ok'=>true];
    }
}
