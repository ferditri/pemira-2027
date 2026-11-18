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

    protected $with = ['candidate'];

    public function getNameAttribute($value)
    {
        if ($this->candidate) {
            return $this->candidate->name_2
                ? "{$this->candidate->name_1} & {$this->candidate->name_2}"
                : $this->candidate->name_1;
        }
        return $value;
    }

    public function getPhotoAttribute($value)
    {
        if ($this->candidate) {
            return $this->candidate->picture;
        }
        return $value;
    }

    public function getVisionAttribute($value)
    {
        if ($this->candidate) {
            return $this->candidate->vision;
        }
        return $value;
    }

    public function getMissionAttribute($value)
    {
        if ($this->candidate) {
            $lines = preg_split('/\r\n|\n|\r/', (string) $this->candidate->mission) ?: [];
            $cleaned = array_values(array_filter(array_map('trim', $lines), fn ($line) => $line !== ''));
            return $cleaned ?: [$this->candidate->mission];
        }
        return $this->castAttribute('mission', $value);
    }

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }
}
