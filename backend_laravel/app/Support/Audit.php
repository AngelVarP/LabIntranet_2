<?php
namespace App\Support;

use Illuminate\Support\Facades\DB;

class Audit {
  public static function log($userId,$accion,$entidad,$entidadId,$detalles=null){
    DB::table('auditoria')->insert([
      'usuario_id'=>$userId,
      'accion'=>$accion,
      'entidad'=>$entidad,
      'entidad_id'=>$entidadId,
      'detalles'=>$detalles ? json_encode($detalles) : null,
      'creado_at'=>now()
    ]);
  }
}
