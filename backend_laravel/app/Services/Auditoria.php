<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;

class Auditoria {
    public static function log(?int $userId, string $accion, ?string $entidad=null, ?int $entidadId=null, $detalles=null): void {
        DB::table('auditoria')->insert([
            'usuario_id' => $userId,
            'accion'     => $accion,
            'entidad'    => $entidad,
            'entidad_id' => $entidadId,
            'detalles'   => $detalles ? json_encode($detalles) : null,
            'creado_at'  => now(),
        ]);
    }
}
