<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::create('refresh_tokens', function (Blueprint $table) {
        $table->id();
        $table->foreignId('usuario_id')->constrained('usuarios');
        $table->string('token_hash');
        $table->timestamp('expira_en');
        $table->boolean('usado')->default(false);
        $table->boolean('revocado')->default(false);
        $table->timestamp('creado_en')->useCurrent();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refres_tokens');
    }
};
