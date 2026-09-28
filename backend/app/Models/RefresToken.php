<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefreshToken extends Model
{
    public $timestamps = false;
    protected $table = 'refresh_tokens';
    protected $fillable = ['usuario_id', 'token_hash', 'expira_en', 'usado', 'revocado'];
    protected $casts = ['expira_en' => 'datetime', 'usado' => 'boolean', 'revocado' => 'boolean'];
}
