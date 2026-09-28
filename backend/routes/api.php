<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ConsultaController;
use App\Http\Controllers\Api\PaisController;
use Illuminate\Support\Facades\Route;

// Rutas Públicas
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:login');
Route::post('/auth/refresh', [AuthController::class, 'refresh']);

// Rutas Protegidas por Token Middleware
Route::middleware('auth.token')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    
    Route::get('/paises', [PaisController::class, 'index']);
    Route::get('/paises/{id}/ciudades', [PaisController::class, 'ciudades']);
    
    Route::post('/consultas', [ConsultaController::class, 'consultar']);
    Route::get('/consultas/historial', [ConsultaController::class, 'historial']);
});