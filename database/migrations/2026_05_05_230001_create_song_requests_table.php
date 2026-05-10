<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('song_requests', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->string('artist', 200)->nullable();
            $table->enum('status', ['pending', 'approved', 'played', 'rejected'])->default('pending');
            $table->integer('votes')->default(1);
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('votes');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('song_requests');
    }
};
