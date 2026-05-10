<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('michelada_recipes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedSmallInteger('beer_volume_ml')->default(600);
            $table->unsignedSmallInteger('other_volume_ml')->default(200);
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('caguamas', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('total_volume_ml')->default(1200);
            $table->unsignedSmallInteger('remaining_volume_ml')->default(1200);
            $table->timestamp('opened_at');
            $table->foreignId('opened_by')->constrained('users')->restrictOnDelete();
            $table->enum('status', ['active', 'empty'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('beer_pours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caguama_id')->constrained()->cascadeOnDelete();
            $table->foreignId('michelada_recipe_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('volume_ml');
            $table->foreignId('poured_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('order_item_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beer_pours');
        Schema::dropIfExists('caguamas');
        Schema::dropIfExists('michelada_recipes');
    }
};
