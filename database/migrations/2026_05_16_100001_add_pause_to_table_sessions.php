<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('table_sessions', function (Blueprint $table) {
            // MySQL enum modification: drop old, add new with 'paused'
            $table->enum('status', ['active', 'paused', 'closed', 'cancelled'])
                ->default('active')
                ->change();
            $table->timestamp('paused_at')->nullable()->after('status');
            $table->unsignedInteger('paused_minutes')->default(0)->after('paused_at');
        });
    }

    public function down(): void
    {
        Schema::table('table_sessions', function (Blueprint $table) {
            $table->dropColumn(['paused_at', 'paused_minutes']);
            $table->enum('status', ['active', 'closed', 'cancelled'])
                ->default('active')
                ->change();
        });
    }
};
