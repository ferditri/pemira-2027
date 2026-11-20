<?php

namespace App\Http\Controllers;

use App\Models\UserVerification;
use Illuminate\Http\Request;
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

        // Nonaktifkan record lama tanpa menghapus (simpan riwayat)
        $user->verificationHistories()
            ->where("is_active", true)
            ->update(["is_active" => false]);

        $user->verificationHistories()->create([
            "ktm" => $ktm,
            "verification" => $verification,
            "status" => "pending",
            "is_active" => true,
        ]);

        return redirect()->route("index")
            ->with("flash.message", "Foto verifikasi berhasil dikirim dan menunggu validasi panitia.")
            ->with("flash.type", "success");
    }
}
