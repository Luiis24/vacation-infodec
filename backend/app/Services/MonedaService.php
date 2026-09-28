<?php

namespace App\Services;

use App\Models\TasaCambio;
use Illuminate\Support\Facades\Http;
use Exception;

class MonedaService
{
    public function obtenerTasa(int $monedaId, string $codigoMoneda): ?float
    {
        try {
            $apiKey = env('EXCHANGE_API_KEY');
            $response = Http::timeout(4)->get("https://v6.exchangerate-api.com/v6/{$apiKey}/latest/COP");

            if ($response->successful()) {
                $rates = $response->json()['conversion_rates'] ?? [];
                if (isset($rates[$codigoMoneda])) {
                    $tasa = (float) $rates[$codigoMoneda];

                    // Guardar en base de datos para historial y fallback
                    TasaCambio::create([
                        'moneda_id' => $monedaId,
                        'tasa' => $tasa,
                        'fecha' => now()
                    ]);

                    return $tasa;
                }
            }
        } catch (Exception $e) {
            // En caso de fallo de la API, consulta la última tasa guardada
        }

        // FALLBACK: Obtener la última tasa registrada
        $ultimaTasa = TasaCambio::where('moneda_id', $monedaId)
            ->orderBy('fecha', 'desc')
            ->first();

        return $ultimaTasa ? (float) $ultimaTasa->tasa : null;
    }
}