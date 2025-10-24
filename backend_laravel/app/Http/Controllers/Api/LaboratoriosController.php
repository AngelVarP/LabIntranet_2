<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaboratoriosController extends Controller
{
    private function mustTechOrAdmin(Request $r){
        $u = $r->user(); if(!$u || !$u->hasAnyRole(['admin','tecnico'])) abort(403,'No autorizado');
    }

    // GET /api/laboratorios?q=&per_page=
    public function index(Request $r){
        $q   = trim((string)$r->query('q',''));
        $per = max(1, min((int)$r->query('per_page', 20), 100));

        $qry = DB::table('laboratorios')
            ->when($q !== '', function($qq) use($q){
                $like = '%'.$q.'%';
                $qq->where(function($w) use($like){
                    $w->where('nombre','like',$like)
                      ->orWhere('codigo','like',$like)
                      ->orWhere('ubicacion','like',$like);
                });
            })
            ->orderBy('nombre');

        return $qry->paginate($per, ['id','codigo','nombre','aforo','ubicacion']);
    }

    // POST /api/laboratorios  {codigo, nombre, aforo?, ubicacion?}
    public function store(Request $r){
        $this->mustTechOrAdmin($r);

        $data = $r->validate([
            'codigo'    => ['required','string','max:40'],
            'nombre'    => ['required','string','max:120'],
            'aforo'     => ['nullable','integer','min:0'],
            'ubicacion' => ['nullable','string','max:140'],
        ]);

        // Unicidad por 'codigo' (tu tabla lo tiene UNIQUE)
        $exists = DB::table('laboratorios')->where('codigo',$data['codigo'])->exists();
        if($exists) return response()->json(['error'=>'Ya existe un laboratorio con ese código'], 422);

        $id = DB::table('laboratorios')->insertGetId([
            'codigo'    => $data['codigo'],
            'nombre'    => $data['nombre'],
            'aforo'     => $data['aforo'] ?? null,
            'ubicacion' => $data['ubicacion'] ?? null,
            // 'creado_at' lo pone MySQL por defecto
        ]);

        return response()->json(['ok'=>true,'id'=>$id], 201);
    }

    // PUT /api/laboratorios/{id}  {codigo, nombre, aforo?, ubicacion?}
    public function update(Request $r, int $id){
        $this->mustTechOrAdmin($r);

        $data = $r->validate([
            'codigo'    => ['required','string','max:40'],
            'nombre'    => ['required','string','max:120'],
            'aforo'     => ['nullable','integer','min:0'],
            'ubicacion' => ['nullable','string','max:140'],
        ]);

        // Evitar duplicado de código
        $dup = DB::table('laboratorios')
            ->where('codigo',$data['codigo'])
            ->where('id','<>',$id)
            ->exists();
        if($dup) return response()->json(['error'=>'Código en uso por otro laboratorio'], 422);

        $ok = DB::table('laboratorios')->where('id',$id)->update([
            'codigo'    => $data['codigo'],
            'nombre'    => $data['nombre'],
            'aforo'     => $data['aforo'] ?? null,
            'ubicacion' => $data['ubicacion'] ?? null,
        ]);

        if(!$ok) abort(404);
        return ['ok'=>true];
    }

    // DELETE /api/laboratorios/{id}
    public function destroy(Request $r, int $id){
        $this->mustTechOrAdmin($r);

        // Guardas para no romper FK ni lógica:
        $enUso =
            DB::table('practicas')->where('laboratorio_id',$id)->exists() ||
            DB::table('insumo_lotes')->where('laboratorio_id',$id)->exists() ||
            DB::table('equipos')->where('laboratorio_id',$id)->exists() ||
            DB::table('kardex_movimientos')->where('laboratorio_id',$id)->exists() ||
            DB::table('solicitudes')->where('laboratorio_id',$id)->exists();

        if($enUso) {
            return response()->json(['error'=>'No se puede eliminar: el laboratorio está referenciado en otras entidades'], 422);
        }

        $ok = DB::table('laboratorios')->where('id',$id)->delete();
        if(!$ok) abort(404);
        return ['ok'=>true];
    }
}
