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
        Schema::create('patient_amr_organisms', function (Blueprint $table) {
            $table->id();
            $table->string('hn', 20)->index();
            $table->string('regist_flag', 20)->nullable()->index();
            $table->string('ward_id', 20)->nullable();
            $table->char('cre', 1)->default('N');
            $table->char('crab', 1)->default('N');
            $table->char('crpa', 1)->default('N');
            $table->char('mrsa', 1)->default('N');
            $table->char('vre', 1)->default('N');
            $table->char('esbl', 1)->default('N');
            $table->char('c_auris', 1)->default('N');
            $table->string('created_by', 100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_amr_organisms');
    }
};
