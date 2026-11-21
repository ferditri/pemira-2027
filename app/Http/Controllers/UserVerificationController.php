<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class UserVerificationController extends Controller
{

    public function index(Request $request)
    {
        $user = $request->user();
        $active = $user->verification;

        if ($active !== null && !in_array($active->status, ["retake", "rejected"], true)) {
            return redirect()->route("index");
        }

        return Inertia::render("vote/verification", [
            "verification" => $active,
            "exampleImages" => UserVerification::exampleUrls(),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $active = $user->verification;

        if ($active !== null && !in_array($active->status, ["retake", "rejected"], true)) {
            return redirect()->route("index");
        }

        $request->validate([
            "ktm" => "required|image|mimes:jpeg,png,jpg,webp|max:5120", // Maks 5MB & tipe gambar valid
            "verification" => "required|image|mimes:jpeg,png,jpg,webp|max:5120",
        ]);

        $ktm = Storage::disk("local")->put("ktms", $request->file("ktm"));
        $verification = Storage::disk("local")->put(
            "verifications",
            $request->file("verification")
        );

        // Kunci baris user agar request submit paralel (double-click) tidak membuat 2 verifikasi aktif sekaligus
        $created = DB::transaction(function () use ($user, $ktm, $verification) {
            $lockedUser = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $currentActive = $lockedUser->verification;

            if ($currentActive !== null && !in_array($currentActive->status, ["retake", "rejected"], true)) {
                return false;
            }

            $lockedUser->verificationHistories()
                ->where("is_active", true)
                ->update(["is_active" => false]);

            $lockedUser->verificationHistories()->create([
                "ktm" => $ktm,
                "verification" => $verification,
                "status" => "pending",
                "is_active" => true,
            ]);

            return true;
        });

        if (!$created) {
            if ($ktm) {
                Storage::disk("local")->delete($ktm);
            }
            if ($verification) {
                Storage::disk("local")->delete($verification);
            }

            return redirect()->route("index");
        }

        return redirect()->route("index")
            ->with("flash.message", "Foto verifikasi berhasil dikirim dan menunggu validasi panitia.")
            ->with("flash.type", "success");
    }
}
