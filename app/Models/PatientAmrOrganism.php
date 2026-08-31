<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PatientAmrOrganism extends Model
{
    protected $table = 'patient_amr_organisms';

    protected $fillable = [
        'hn',
        'regist_flag',
        'ward_id',

        'created_by',
    ];

    public function selectedOrganisms(): BelongsToMany
    {
        return $this->belongsToMany(
            AmrOrganismMaster::class,
            'patient_amr_organism_selections',
            'patient_amr_organism_id',
            'amr_organism_master_id'
        )->withTimestamps()->orderBy('sort_order')->orderBy('amr_organisms_master.id');
    }
}
