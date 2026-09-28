<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pais;
use App\Models\Ciudad;

class PaisController extends Controller
{
    /** GET /api/paises */
    public function index()
    {
        $paises = Pais::with('moneda')->get();
        return response()->json(['success' => true, 'data' => $paises]);
    }

    /** GET /api/paises/{id}/ciudades */
    public function ciudades($id)
    {
        $pais = Pais::find($id);
        if (!$pais) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'NOT_FOUND', 'message' => 'El país especificado no existe.']
            ], 404);
        }

        $ciudades = Ciudad::where('pais_id', $id)->get();
        return response()->json(['success' => true, 'data' => $ciudades]);
    }
}