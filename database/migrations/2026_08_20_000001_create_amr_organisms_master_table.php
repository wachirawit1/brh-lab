<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('amr_organisms_master')) {
            Schema::create('amr_organisms_master', function (Blueprint $table) {
                $table->id();
                $table->string('code', 50)->unique();
                $table->string('name', 100);
                $table->string('full_name', 255)->nullable();
                $table->string('severity', 20)->default('critical'); // critical, high, medium, info
                $table->string('color', 30)->default('#ef4444');
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->string('created_by', 100)->nullable();
                $table->timestamps();
            });

            // Seed default 7 pathogens
            $defaults = [
                ['code' => 'cre', 'name' => 'CRE', 'full_name' => 'Carbapenem-Resistant Enterobacteriaceae', 'severity' => 'critical', 'color' => '#dc2626', 'sort_order' => 1],
                ['code' => 'crab', 'name' => 'CRAB', 'full_name' => 'CR. Acinetobacter baumannii', 'severity' => 'critical', 'color' => '#dc2626', 'sort_order' => 2],
                ['code' => 'crpa', 'name' => 'CRPA', 'full_name' => 'CR. Pseudomonas aeruginosa', 'severity' => 'critical', 'color' => '#dc2626', 'sort_order' => 3],
                ['code' => 'mrsa', 'name' => 'MRSA', 'full_name' => 'Methicillin-Resistant S. aureus', 'severity' => 'high', 'color' => '#ea580c', 'sort_order' => 4],
                ['code' => 'vre', 'name' => 'VRE', 'full_name' => 'Vancomycin-Resistant Enterococci', 'severity' => 'high', 'color' => '#ea580c', 'sort_order' => 5],
                ['code' => 'esbl', 'name' => 'ESBL', 'full_name' => 'Extended-Spectrum Beta-Lactamase', 'severity' => 'medium', 'color' => '#d97706', 'sort_order' => 6],
                ['code' => 'c_auris', 'name' => 'C. auris', 'full_name' => 'Candida auris (เชื้อราดื้อยาหลายขนาน)', 'severity' => 'critical', 'color' => '#9333ea', 'sort_order' => 7],
            ];

            foreach ($defaults as $d) {
                DB::table('amr_organisms_master')->insert(array_merge($d, [
                    'is_active' => true,
                    'created_by' => 'system',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('amr_organisms_master');
    }
};
