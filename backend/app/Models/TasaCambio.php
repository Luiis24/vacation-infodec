<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TasaCambio extends Model
{
    use HasFactory;

    protected $table = 'tasas_cambio';
    public $timestamps = false;

    protected $fillable = [
        'moneda_id',
        'tasa',
        'fecha'
    ];

    protected $casts = [
        'tasa'  => 'float',
        'fecha' => 'datetime',
    ];

    public function moneda()
    {
        return $this->belongsTo(Moneda::class, 'moneda_id');
    }
}