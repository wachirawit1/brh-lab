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
        Schema::create('telegram_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('chat_id')->unique(); // เก็บ chat_id ของผู้ใช้
            $table->string('user_name')->nullable(); // เก็บชื่อผู้ใช้ Telegram (ถ้ามี)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_subscribers');
    }
};
