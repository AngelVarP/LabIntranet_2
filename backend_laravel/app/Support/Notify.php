<?php
namespace App\Support;

use Illuminate\Support\Facades\DB;

class Notify {
  public static function push($usuarioId, $titulo, $cuerpo=null, $tipo='INFO', $refEntidad=null, $refId=null){
    DB::table('notificaciones')->insert([
      'usuario_id'=>$usuarioId,
      'tipo'=>$tipo,
      'titulo'=>$titulo,
      'cuerpo'=>$cuerpo,
      'leida'=>0,
      'creado_at'=>now(),
      'ref_entidad'=>$refEntidad,
      'ref_id'=>$refId
    ]);
  }
}
