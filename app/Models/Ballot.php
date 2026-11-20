<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ballot extends Model
{
    use SoftDeletes;
    protected $fillable = [
        "organization_id",
        "user_id",
        "active_lock",
        "ktm",
        "verification",
        "is_verified",
        "verified_at",
        "is_confirmed",
        "confirmed_at",
    ];

    protected $casts = [
        "is_verified" => "boolean",
        "active_lock" => "integer",
    ];

    protected static function booted(): void
    {
        static::creating(function (Ballot $ballot) {
            if ($ballot->active_lock === null && $ballot->deleted_at === null) {
                $ballot->active_lock = 1;
            }
        });

        static::deleting(function (Ballot $ballot) {
            if (!$ballot->isForceDeleting()) {
                $ballot->active_lock = null;
                $ballot->saveQuietly();
            }
        });
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(BallotDetail::class);
    }
}
