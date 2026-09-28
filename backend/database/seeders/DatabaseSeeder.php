<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Monedas
        DB::table('monedas')->insert([
            ['codigo' => 'GBP', 'nombre' => 'Libra esterlina', 'simbolo' => '£'],
            ['codigo' => 'JPY', 'nombre' => 'Yen japonés', 'simbolo' => '¥'],
            ['codigo' => 'INR', 'nombre' => 'Rupia india', 'simbolo' => '₹'],
            ['codigo' => 'DKK', 'nombre' => 'Corona danesa', 'simbolo' => 'kr'],
        ]);

        // Países
        DB::table('paises')->insert([
            ['nombre' => 'Inglaterra', 'codigo' => 'GBR', 'moneda_id' => 1],
            ['nombre' => 'Japón',      'codigo' => 'JPN', 'moneda_id' => 2],
            ['nombre' => 'India',      'codigo' => 'IND', 'moneda_id' => 3],
            ['nombre' => 'Dinamarca',  'codigo' => 'DNK', 'moneda_id' => 4],
        ]);

        // Ciudades (con coordenadas reales para OpenWeatherMap)
        DB::table('ciudades')->insert([
            ['pais_id' => 1, 'nombre' => 'Londres',     'latitud' => 51.5074000, 'longitud' => -0.1278000],
            ['pais_id' => 1, 'nombre' => 'Manchester',  'latitud' => 53.4839590, 'longitud' => -2.2446440],
            ['pais_id' => 2, 'nombre' => 'Tokio',       'latitud' => 35.6895000, 'longitud' => 139.6917100],
            ['pais_id' => 2, 'nombre' => 'Osaka',       'latitud' => 34.6937400, 'longitud' => 135.5021800],
            ['pais_id' => 3, 'nombre' => 'Nueva Delhi', 'latitud' => 28.6139400, 'longitud' => 77.2090200],
            ['pais_id' => 3, 'nombre' => 'Bombay',      'latitud' => 19.0759830, 'longitud' => 72.8776550],
            ['pais_id' => 4, 'nombre' => 'Copenhague',  'latitud' => 55.6761000, 'longitud' => 12.5683000],
            ['pais_id' => 4, 'nombre' => 'Aarhus',      'latitud' => 56.1629390, 'longitud' => 10.2039210],
        ]);

        // Usuario de prueba (contraseña: Password123)
        DB::table('usuarios')->insert([
            'nombre' => 'Usuario Prueba',
            'correo' => 'prueba@infodec.com',
            'password_hash' => Hash::make('Password123'),
            'idioma' => 'es',
        ]);
    }
}