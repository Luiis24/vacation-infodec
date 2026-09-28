<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TokenRevocado extends Model
{
    public $timestamps = false;
    protected $table = 'tokens_revocados';
    protected $fillable = ['jti'];
}