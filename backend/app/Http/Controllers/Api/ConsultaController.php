<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ciudad;
use App\Models\Historial;
use App\Services\ClimaService;
use App\Services\MonedaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ConsultaController extends Controller
{
    public function __construct(
        private ClimaService $climaService,
        private MonedaService $monedaService
    ) {}

    /** POST /api/consultas */
    public function consultar(Request $request)
    {
        $v = Validator::make($request->all(), [
            'ciudad_id' => 'required|integer|exists:ciudades,id',
            'presupuesto' => 'required|numeric|gt:0',
        ]);

        if ($v->fails()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Hay campos con errores.',
                    'details' => $v->errors()
                ]
            ], 422);
        }

        $usuarioId = $request->attributes->get('usuario_id');
        $ciudad = Ciudad::with('pais.moneda')->find($request->ciudad_id);

        $clima = $this->climaService->obtenerClima($ciudad->latitud, $ciudad->longitud);
        $tasa = $this->monedaService->obtenerTasa($ciudad->pais->moneda->id, $ciudad->pais->moneda->codigo);

        if ($tasa === null) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'EXTERNAL_API_ERROR',
                    'message' => 'Conversión no disponible.'
                ]
            ], 502);
        }

        $valorConvertido = round($request->presupuesto * $tasa, 2);

        // Guardar consulta en historial
        $historial = Historial::create([
            'usuario_id' => $usuarioId,
            'ciudad_id' => $ciudad->id,
            'presupuesto_cop' => $request->presupuesto,
            'clima' => $clima,
            'tasa' => $tasa,
            'valor_convertido' => $valorConvertido,
            'fecha' => now()
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'pais' => $ciudad->pais->nombre,
                'ciudad' => $ciudad->nombre,
                'presupuesto_cop' => (float) $request->presupuesto,
                'clima_celsius' => $clima !== null ? "{$clima} °C" : "Clima no disponible",
                'moneda' => $ciudad->pais->moneda->nombre,
                'simbolo' => $ciudad->pais->moneda->simbolo,
                'valor_convertido' => $valorConvertido,
                'tasa_aplicada' => $tasa,
                'fecha' => now()->toIso8601String()
            ]
        ]);
    }

    /** GET /api/consultas/historial */
    public function historial(Request $request)
    {
        $usuarioId = $request->attributes->get('usuario_id');

        $ultimasConsultas = Historial::with(['ciudad.pais.moneda'])
            ->where('usuario_id', $usuarioId)
            ->orderBy('fecha', 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $ultimasConsultas
        ]);
    }
}