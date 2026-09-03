<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql';

    public function up(): void
    {
        Schema::connection('mysql')->create('system_audit_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamp('occurred_at')->useCurrent()->index();
            $table->string('request_id', 64)->index();
            $table->string('category', 50)->index();
            $table->string('event', 100)->index();
            $table->string('action', 255);
            $table->string('result', 20)->index();
            $table->string('actor_username', 100)->nullable()->index();
            $table->string('actor_name', 255)->nullable();
            $table->string('target_type', 80)->nullable();
            $table->string('target_id', 191)->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('old_values')->nullable();
            $table->text('new_values')->nullable();
            $table->text('metadata')->nullable();
        });
    }

    public function down(): void
    {
        Schema::connection('mysql')->dropIfExists('system_audit_logs');
    }
};
