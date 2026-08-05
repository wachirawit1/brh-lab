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
        Schema::table('telegram_subscribers', function (Blueprint $table) {
            if (!Schema::hasColumn('telegram_subscribers', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('telegram_subscribers', function (Blueprint $table) {
            if (Schema::hasColumn('telegram_subscribers', 'created_at')) {
                $table->dropColumn('created_at');
            }
        });
    }
};
