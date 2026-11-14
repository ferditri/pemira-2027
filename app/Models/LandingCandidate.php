<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingCandidate extends Model
{
    protected $fillable = [
        'section',
        'candidate_id',
        'role_label',
        'name',
        'photo',
        'vision',
        'mission',
        'sort_order',
    ];

    protected $casts = [
        'mission' => 'array',
        'sort_order' => 'integer',
    ];

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }
}
