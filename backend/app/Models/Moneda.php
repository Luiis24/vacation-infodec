<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Moneda extends Model
{
    use HasFactory;

    protected $table = 'monedas';
    public $timestamps = false;

    protected $fillable = ['codigo', 'nombre', 'simbolo'];
}