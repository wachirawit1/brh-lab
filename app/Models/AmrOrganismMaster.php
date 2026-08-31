<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AmrOrganismMaster extends Model
{
    protected $table = 'amr_organisms_master';

    protected $fillable = [
        'code',
        'name',
        'full_name',
        'description',
        'severity',
        'color',
        'is_active',
        'sort_order',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    public function patientRecords(): BelongsToMany
    {
        return $this->belongsToMany(
            PatientAmrOrganism::class,
            'patient_amr_organism_selections',
            'amr_organism_master_id',
            'patient_amr_organism_id'
        )->withTimestamps();
    }
}
