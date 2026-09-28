<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pais extends Model
{
    use HasFactory;

    protected $table = 'paises';
    public $timestamps = false;

    protected $fillable = ['nombre', 'codigo', 'moneda_id'];

    public function moneda()
    {
        return $this->belongsTo(Moneda::class, 'moneda_id');
    }

    public function ciudades()
    {
        return $this->hasMany(Ciudad::class, 'pais_id');
    }
}