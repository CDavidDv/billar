<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_tables', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['billar_comun', 'billar_privado', 'futbolito', 'maquina', 'otro'])->default('billar_comun');
            $table->enum('billing_type', ['por_hora', 'precio_fijo'])->default('por_hora');
            $table->decimal('hourly_rate', 8, 2)->default(50.00);
            $table->boolean('is_active')->default(true);
            $table->integer('map_x')->nullable();
            $table->integer('map_y')->nullable();
            $table->integer('map_width')->default(80);
            $table->integer('map_height')->default(80);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_tables');
    }
};
