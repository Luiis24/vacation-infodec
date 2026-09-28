<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ciudad extends Model
{
    use HasFactory;

    protected $table = 'ciudades';
    public $timestamps = false;

    protected $fillable = ['pais_id', 'nombre', 'latitud', 'longitud'];

    public function pais()
    {
        return $this->belongsTo(Pais::class, 'pais_id');
    }
}