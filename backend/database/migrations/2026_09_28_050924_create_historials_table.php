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
    Schema::create('historial', function (Blueprint $table) {
        $table->id();
        $table->foreignId('usuario_id')->constrained('usuarios');
        $table->foreignId('ciudad_id')->constrained('ciudades');
        $table->decimal('presupuesto_cop', 15, 2);
        $table->decimal('clima', 6, 2)->nullable();
        $table->decimal('tasa', 20, 10)->nullable();
        $table->decimal('valor_convertido', 20, 2)->nullable();
        $table->timestamp('fecha')->useCurrent();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historials');
    }
};
