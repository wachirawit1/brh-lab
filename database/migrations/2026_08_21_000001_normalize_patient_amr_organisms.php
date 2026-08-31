<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('amr_organisms_master', 'description')) {
            Schema::table('amr_organisms_master', function (Blueprint $table) {
                $table->text('description')->nullable();
            });
        }

        if (! Schema::hasTable('patient_amr_organism_selections')) {
            Schema::create('patient_amr_organism_selections', function (Blueprint $table) {
                $table->id();
                $table->foreignId('patient_amr_organism_id')->constrained('patient_amr_organisms')->cascadeOnDelete();
                $table->foreignId('amr_organism_master_id')->constrained('amr_organisms_master')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['patient_amr_organism_id', 'amr_organism_master_id'], 'patient_amr_selection_unique');
            });
        }

        $organisms = [
            ['code' => 'crab', 'name' => 'CRAB', 'full_name' => 'Carbapenem-resistant Acinetobacter baumannii', 'severity' => 'critical', 'color' => '#dc2626'],
            ['code' => 'crpa', 'name' => 'CRPA', 'full_name' => 'Carbapenem-resistant Pseudomonas aeruginosa', 'severity' => 'critical', 'color' => '#dc2626'],
            ['code' => 'crkp', 'name' => 'CRKP', 'full_name' => 'Carbapenem-resistant Klebsiella pneumoniae', 'severity' => 'critical', 'color' => '#dc2626'],
            ['code' => 'crec', 'name' => 'CREC', 'full_name' => 'Carbapenem-resistant Escherichia coli', 'severity' => 'critical', 'color' => '#dc2626'],
            ['code' => 'coro', 'name' => 'CoRO', 'full_name' => 'Colistin-resistant organisms', 'severity' => 'critical', 'color' => '#be123c'],
            ['code' => 'escr', 'name' => 'ESCR', 'full_name' => 'Extended-Spectrum Cephalosporin-Resistant organisms', 'severity' => 'high', 'color' => '#ea580c'],
            ['code' => 'mrsa', 'name' => 'MRSA', 'full_name' => 'Methicillin-resistant Staphylococcus aureus', 'severity' => 'high', 'color' => '#ea580c'],
            ['code' => 'visa_vrsa', 'name' => 'VISA/VRSA', 'full_name' => 'Vancomycin intermediate/resistant Staphylococcus aureus', 'severity' => 'critical', 'color' => '#b91c1c'],
            ['code' => 'salmonr', 'name' => 'SalmonR', 'full_name' => 'Fluoroquinolone or Extended-Spectrum Cephalosporin-Resistant Salmonella spp.', 'severity' => 'high', 'color' => '#d97706'],
            ['code' => 'drsp', 'name' => 'DRSP', 'full_name' => 'Penicillin/Ampicillin/Macrolide/ESC-resistant Streptococcus pneumoniae', 'severity' => 'high', 'color' => '#d97706'],
            ['code' => 'vre', 'name' => 'VRE', 'full_name' => 'Vancomycin-resistant Enterococcus faecium / Enterococcus faecalis', 'severity' => 'high', 'color' => '#7e22ce'],
            ['code' => 'mrcons', 'name' => 'MRCoNS', 'full_name' => 'Methicillin-resistant coagulase-negative Staphylococci', 'severity' => 'medium', 'color' => '#0369a1', 'description' => 'S. epidermidis, S. haemolyticus, S. hominis, S. capitis, S. lugdunensis และ S. saprophyticus'],
            ['code' => 'strepr', 'name' => 'StrepR', 'full_name' => 'Streptococci-resistant microorganisms', 'severity' => 'medium', 'color' => '#047857', 'description' => 'S. pyogenes, S. agalactiae, S. dysgalactiae, S. suis, S. gallolyticus, S. anginosus, S. sanguinis, S. mitis และ S. oralis'],
            ['code' => 'other', 'name' => 'Other', 'full_name' => 'เชื้อดื้อยาอื่นที่พบน้อยแต่มีความรุนแรงทางคลินิก', 'severity' => 'info', 'color' => '#475569', 'description' => 'Aeromonas spp.; Other Enterobacterales; Stenotrophomonas maltophilia; other non-fermenters; Corynebacterium jeikeium/striatum; Elizabethkingia spp.; Neisseria gonorrhoeae; Burkholderia cepacia complex ตามเกณฑ์การดื้อยาที่กำหนด'],
        ];

        foreach ($organisms as $index => $organism) {
            DB::table('amr_organisms_master')->updateOrInsert(
                ['code' => $organism['code']],
                array_merge($organism, [
                    'is_active' => true,
                    'sort_order' => $index + 1,
                    'updated_at' => now(),
                ])
            );
        }

        DB::table('amr_organisms_master')
            ->whereNotIn('code', array_column($organisms, 'code'))
            ->update(['is_active' => false, 'updated_at' => now()]);

        $legacyColumns = ['cre', 'crab', 'crpa', 'mrsa', 'vre', 'esbl', 'c_auris'];
        foreach ($legacyColumns as $code) {
            $masterId = DB::table('amr_organisms_master')->where('code', $code)->value('id');
            if (! $masterId || ! Schema::hasColumn('patient_amr_organisms', $code)) {
                continue;
            }

            DB::table('patient_amr_organisms')->where($code, 'Y')->orderBy('id')->each(function ($record) use ($masterId) {
                DB::table('patient_amr_organism_selections')->updateOrInsert(
                    ['patient_amr_organism_id' => $record->id, 'amr_organism_master_id' => $masterId],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_amr_organism_selections');

        if (Schema::hasColumn('amr_organisms_master', 'description')) {
            Schema::table('amr_organisms_master', function (Blueprint $table) {
                $table->dropColumn('description');
            });
        }
    }
};
