<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class ClimaService
{
    public function obtenerClima(float $lat, float $lon): ?float
    {
        try {
            $apiKey = env('WEATHER_API_KEY');
            if (!$apiKey) return null;

            $response = Http::timeout(4)->get("https://api.openweathermap.org/data/2.5/weather", [
                'lat' => $lat,
                'lon' => $lon,
                'appid' => $apiKey,
                'units' => 'metric'
            ]);

            if ($response->successful()) {
                return $response->json()['main']['temp'] ?? null;
            }
        } catch (Exception $e) {
            // Falla de API de clima
        }
        return null;
    }
}