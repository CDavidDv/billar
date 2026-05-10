<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('screen_contents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('type', ['youtube', 'image', 'stream', 'text'])->default('text');
            $table->text('content');
            $table->boolean('is_active')->default(false);
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('scheduled_end_at')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('screen_contents');
    }
};
