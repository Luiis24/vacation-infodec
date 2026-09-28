<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Historial extends Model
{
    use HasFactory;

    protected $table = 'historial';
    public $timestamps = false;

    protected $fillable = [
        'usuario_id',
        'ciudad_id',
        'presupuesto_cop',
        'clima',
        'tasa',
        'valor_convertido',
        'fecha'
    ];

    protected $casts = [
        'presupuesto_cop'  => 'float',
        'clima'            => 'float',
        'tasa'             => 'float',
        'valor_convertido' => 'float',
        'fecha'            => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function ciudad()
    {
        return $this->belongsTo(Ciudad::class, 'ciudad_id');
    }
}