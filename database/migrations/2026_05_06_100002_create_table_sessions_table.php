<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('table_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_table_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('opened_by')->constrained('users');
            $table->unsignedBigInteger('closed_by')->nullable();
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->enum('billing_type', ['por_hora', 'precio_fijo'])->default('por_hora');
            $table->decimal('hourly_rate', 8, 2);
            $table->integer('time_minutes')->nullable();
            $table->decimal('time_cost', 10, 2)->nullable();
            $table->enum('status', ['active', 'closed', 'cancelled'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('table_sessions');
    }
};
