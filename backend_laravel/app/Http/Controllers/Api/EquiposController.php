<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EquiposController extends Controller
{
    // GET /api/equipos?q=&laboratorio_id=&activo=&page=&per_page=
    public function index(Request $request)
    {
        $perPage = (int) ($request->input('per_page', 12));

        $q = DB::table('equipos as e')
            ->leftJoin('laboratorios as l', 'l.id', '=', 'e.laboratorio_id')
            ->select('e.*', 'l.nombre as laboratorio_nombre');

        if ($request->filled('q')) {
            $text = '%' . $request->q . '%';
            $q->where(function ($qq) use ($text) {
                $qq->where('e.codigo', 'like', $text)
                   ->orWhere('e.nombre', 'like', $text)
                   ->orWhere('e.serie',  'like', $text);
            });
        }

        if ($request->filled('laboratorio_id')) {
            $q->where('e.laboratorio_id', $request->laboratorio_id);
        }

        // Aplica filtro "activo" SÓLO si la columna existe (evita SQL error si no la tienes)
        if ($request->filled('activo') && Schema::hasColumn('equipos', 'activo')) {
            $q->where('e.activo', (int) $request->activo);
        }

        $data = $q->orderByDesc('e.id')->paginate($perPage)->withQueryString();

        return response()->json($data);
    }

    // GET /api/equipos/{id}
    public function show(int $id)
    {
        $row = DB::table('equipos as e')
            ->leftJoin('laboratorios as l', 'l.id', '=', 'e.laboratorio_id')
            ->select('e.*', 'l.nombre as laboratorio_nombre')
            ->where('e.id', $id)
            ->first();

        abort_if(!$row, 404, 'Equipo no encontrado');

        return response()->json($row);
    }

    // POST /api/equipos
    public function store(Request $request)
    {
        $payload = $request->validate([
            'codigo'          => ['nullable','string','max:100'],
            'nombre'          => ['required','string','max:255'],
            'nro_serie'       => ['nullable','string','max:150'],
            'laboratorio_id'  => ['nullable','integer'],
            'descripcion'     => ['nullable','string'],
            'activo'          => ['nullable','boolean'],
        ]);

        // tu tabla usa "serie", mapeamos nro_serie -> serie
        $payload['serie'] = $payload['nro_serie'] ?? null;
        unset($payload['nro_serie']);

        $payload['created_at'] = now();
        $payload['updated_at'] = now();

        $id = DB::table('equipos')->insertGetId($payload);

        return response()->json(['id' => $id], 201);
    }

    // PUT /api/equipos/{id}
    public function update(Request $request, int $id)
    {
        $payload = $request->validate([
            'codigo'          => ['nullable','string','max:100'],
            'nombre'          => ['nullable','string','max:255'],
            'nro_serie'       => ['nullable','string','max:150'],
            'laboratorio_id'  => ['nullable','integer'],
            'descripcion'     => ['nullable','string'],
            'activo'          => ['nullable','boolean'],
        ]);

        if (array_key_exists('nro_serie', $payload)) {
            $payload['serie'] = $payload['nro_serie'];
            unset($payload['nro_serie']);
        }

        $payload['updated_at'] = now();

        $ok = DB::table('equipos')->where('id', $id)->update($payload);

        abort_if(!$ok, 404, 'Equipo no encontrado');

        return response()->json(['updated' => true]);
    }

    // DELETE /api/equipos/{id}
    public function destroy(int $id)
    {
        $ok = DB::table('equipos')->where('id', $id)->delete();
        abort_if(!$ok, 404, 'Equipo no encontrado');

        return response()->json(['deleted' => true]);
    }
}
