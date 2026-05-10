<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('add_ins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedSmallInteger('volume_ml')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('beer_pour_add_ins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beer_pour_id')->constrained()->cascadeOnDelete();
            $table->foreignId('add_in_id')->constrained()->restrictOnDelete();
            $table->unsignedSmallInteger('volume_ml');
        });

        Schema::table('michelada_recipes', function (Blueprint $table) {
            $table->unsignedSmallInteger('container_volume_ml')->default(800)->after('beer_volume_ml');
        });
    }

    public function down(): void
    {
        Schema::table('michelada_recipes', function (Blueprint $table) {
            $table->dropColumn('container_volume_ml');
        });
        Schema::dropIfExists('beer_pour_add_ins');
        Schema::dropIfExists('add_ins');
    }
};
