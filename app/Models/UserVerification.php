<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class UserVerification extends Model
{
    protected $fillable = [
        "user_id",
        "ktm",
        "verification",
        "status",
        "is_active",
        "admin_note",
        "reviewed_by",
        "reviewed_at",
    ];

    protected function casts(): array
    {
        return [
            "is_active" => "boolean",
            "reviewed_at" => "datetime",
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, "reviewed_by");
    }

    public static function exampleUrls(): array
    {
        $files = Storage::disk("public")->files("examples");
        $ktm = collect($files)->first(fn ($f) => str_starts_with(basename($f), "ktm."));
        $selfie = collect($files)->first(fn ($f) => str_starts_with(basename($f), "selfie."));

        return [
            "ktm" => $ktm ? "/storage/" . $ktm . "?v=" . Storage::disk("public")->lastModified($ktm) : null,
            "selfie" => $selfie ? "/storage/" . $selfie . "?v=" . Storage::disk("public")->lastModified($selfie) : null,
        ];
    }
}
