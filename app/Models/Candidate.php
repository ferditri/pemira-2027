<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    protected $fillable = [
        "organization_id",
        "group_id",
        "name_1",
        "name_2",
        "vision",
        "mission",
        "picture",
    ];

    protected static function booted(): void
    {
        static::saved(function (Candidate $candidate) {
            $name = $candidate->name_2 ? "{$candidate->name_1} & {$candidate->name_2}" : $candidate->name_1;
            $lines = preg_split('/\r\n|\n|\r/', (string) $candidate->mission) ?: [];
            $mission = array_values(array_filter(array_map('trim', $lines), fn ($line) => $line !== '')) ?: [$candidate->mission];

            LandingCandidate::where('candidate_id', $candidate->id)->update([
                'name' => $name,
                'photo' => $candidate->picture,
                'vision' => $candidate->vision,
                'mission' => json_encode($mission),
            ]);
        });

        static::deleted(function (Candidate $candidate) {
            LandingCandidate::where('candidate_id', $candidate->id)->delete();
        });
    }

    public function landingCandidates()
    {
        return $this->hasMany(LandingCandidate::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function ballotDetails()
    {
        return $this->hasMany(BallotDetail::class);
    }

    public function ballots()
    {
        return $this->hasManyThrough(
            Ballot::class,
            BallotDetail::class,
            "candidate_id",
            "id",
            "id",
            "ballot_id"
        );
    }

    public function verified()
    {
        return $this->ballots->where("is_verified", true);
    }
}
