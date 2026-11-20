<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = [
        "npm",
        "email",
        "major",
        "year",
        "name",
        "type",
        "google_id",
        "avatar",
    ];

    protected $hidden = [
        "google_id",
    ];

    public function verification()
    {
        return $this->hasOne(UserVerification::class)
            ->where("is_active", true)
            ->latestOfMany();
    }

    public function verificationHistories()
    {
        return $this->hasMany(UserVerification::class)
            ->orderByDesc("created_at");
    }

    public function ballot()
    {
        return $this->hasOne(Ballot::class);
    }

    public function ballots()
    {
        return $this->hasMany(Ballot::class);
    }

    public function isSuperAdmin(): bool
    {
        return in_array(
            strtolower((string) $this->email),
            config("pemira.admin_emails", []),
            true
        );
    }
}
