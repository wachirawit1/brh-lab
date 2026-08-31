<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const LEGACY_COLUMNS = ['cre', 'crab', 'crpa', 'mrsa', 'vre', 'esbl', 'c_auris'];

    public function up(): void
    {
        if (! Schema::hasTable('patient_amr_organisms')) {
            return;
        }

        $existingColumns = array_values(array_filter(
            self::LEGACY_COLUMNS,
            fn (string $column) => Schema::hasColumn('patient_amr_organisms', $column)
        ));

        if ($existingColumns === []) {
            return;
        }

        if (! Schema::hasTable('patient_amr_organism_selections') || ! Schema::hasTable('amr_organisms_master')) {
            throw new RuntimeException('Cannot remove legacy AMR columns before the normalized AMR tables exist.');
        }

        $this->copyLegacySelections();

        Schema::table('patient_amr_organisms', function (Blueprint $table) use ($existingColumns) {
            $table->dropColumn($existingColumns);
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('patient_amr_organisms')) {
            return;
        }

        Schema::table('patient_amr_organisms', function (Blueprint $table) {
            foreach (self::LEGACY_COLUMNS as $column) {
                if (! Schema::hasColumn('patient_amr_organisms', $column)) {
                    $table->char($column, 1)->default('N');
                }
            }
        });

        if (! Schema::hasTable('patient_amr_organism_selections') || ! Schema::hasTable('amr_organisms_master')) {
            return;
        }

        foreach (self::LEGACY_COLUMNS as $code) {
            $masterId = DB::table('amr_organisms_master')->where('code', $code)->value('id');
            if (! $masterId) {
                continue;
            }

            DB::table('patient_amr_organism_selections')
                ->where('amr_organism_master_id', $masterId)
                ->pluck('patient_amr_organism_id')
                ->each(fn ($recordId) => DB::table('patient_amr_organisms')->where('id', $recordId)->update([$code => 'Y']));
        }
    }

    private function copyLegacySelections(): void
    {
        if (! Schema::hasTable('patient_amr_organism_selections') || ! Schema::hasTable('amr_organisms_master')) {
            return;
        }

        foreach (self::LEGACY_COLUMNS as $code) {
            if (! Schema::hasColumn('patient_amr_organisms', $code)) {
                continue;
            }

            $masterId = DB::table('amr_organisms_master')->where('code', $code)->value('id');
            if (! $masterId) {
                continue;
            }

            DB::table('patient_amr_organisms')
                ->where($code, 'Y')
                ->orderBy('id')
                ->each(function ($record) use ($masterId) {
                    DB::table('patient_amr_organism_selections')->updateOrInsert(
                        [
                            'patient_amr_organism_id' => $record->id,
                            'amr_organism_master_id' => $masterId,
                        ],
                        ['created_at' => now(), 'updated_at' => now()]
                    );
                });
        }
    }
};
