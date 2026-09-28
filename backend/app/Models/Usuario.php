<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    protected $table = 'usuarios';

    protected $fillable = ['nombre', 'correo', 'password_hash', 'idioma'];

    protected $hidden = ['password_hash'];
}