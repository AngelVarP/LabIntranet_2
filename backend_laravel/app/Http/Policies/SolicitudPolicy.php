<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\DB;

class SolicitudPolicy
{
    public function view(User $user, int $solicitudId): Response
    {
        if ($user->hasRole('admin')) return Response::allow();

        if ($user->hasRole('tecnico')) {
            $ok = DB::table('solicitudes as s')
                ->join('laboratorios as l','l.id','=','s.laboratorio_id')
                ->where('s.id', $solicitudId)
                ->exists(); // refina si asignas labs a técnicos
            return $ok ? Response::allow() : Response::deny('No pertenece a su laboratorio');
        }

        if ($user->hasRole('alumno')) {
            $row = DB::table('solicitudes as s')
                ->join('grupos as g','g.id','=','s.grupo_id')
                ->where('s.id', $solicitudId)
                ->select('g.delegado_usuario_id')
                ->first();
            if ($row && (int)$row->delegado_usuario_id === (int)$user->id) return Response::allow();
            return Response::deny('No eres el delegado actual del grupo');
        }

        if ($user->hasRole('profesor')) {
            return Response::deny('El profesor no accede a solicitudes');
        }

        return Response::deny('Sin permisos');
    }

    public function create(User $user, int $grupoId): Response
    {
        if ($user->hasRole('admin')) return Response::allow();

        if ($user->hasRole('alumno')) {
            $ok = DB::table('grupos')
                ->where('id', $grupoId)
                ->where('delegado_usuario_id', $user->id)
                ->exists();
            return $ok ? Response::allow() : Response::deny('Solo el delegado actual puede crear solicitudes del grupo');
        }

        return Response::deny('Sin permisos');
    }
}
