<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefreshToken extends Model
{
    use HasFactory;

    protected $table = 'refresh_tokens';

    public $timestamps = false;

    protected $fillable = [
        'usuario_id',
        'token_hash',
        'expira_en',
        'revocado',
        'usado'
    ];

    protected $casts = [
        'revocado' => 'boolean',
        'usado'    => 'boolean',
        'expira_en' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}