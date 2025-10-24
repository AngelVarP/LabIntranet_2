<?php
namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class CatalogosController extends Controller
{
    public function categoriasInsumo(){
        return DB::table('categorias_insumo')->orderBy('nombre')->get(['id','nombre']);
    }
    public function laboratorios(){
        return DB::table('laboratorios')->orderBy('nombre')->get(['id','nombre','codigo']);
    }
    public function estadosSolicitud(){
        // enumeración fija del esquema
        return [
            'BORRADOR','PENDIENTE','APROBADO','RECHAZADO','PREPARADO','ENTREGADO','CERRADO'
        ];
    }

}
