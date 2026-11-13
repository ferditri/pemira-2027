<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Whitelist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver("google")->redirect();
    }

    public function callback(Request $request)
    {
        try {
            $user = Socialite::driver("google")->user();
        } catch (\Throwable $th) {
            \Illuminate\Support\Facades\Log::error("Google OAuth error: " . $th->getMessage());
            return redirect(route("login"))
                ->with("flash.message", "Gagal autentikasi Google: " . $th->getMessage())
                ->with("flash.type", "destructive");
        }

        $domain = explode("@", $user->email)[1] ?? "";
        $allowAnyEmail = env("AUTH_ALLOW_ANY_EMAIL", false);

        if ($domain !== "student.upnjatim.ac.id" && !$allowAnyEmail) {
            return redirect(route("login"))
                ->with("flash.message", "Oops.. kamu harus menggunakan email mahasiswa UPN!")
                ->with("flash.type", "destructive");
        }

        $npm = explode("@", $user->email)[0];

        // Jika mode bebas email diaktifkan di .env: otomatis whitelist agar mudah dites
        if ($allowAnyEmail) {
            Whitelist::query()->firstOrCreate(["npm" => $npm]);
        }

        if (Whitelist::query()->find($npm) === null) {
            return redirect(route("login"))
                ->with("flash.message", "Maaf.. NPM kamu tidak terdaftar sebagai pemilih 😔")
                ->with("flash.type", "destructive");
        }

        $year = (strlen($npm) >= 2 && is_numeric(substr($npm, 0, 2))) ? substr($npm, 0, 2) : "22";
        $major = (strlen($npm) >= 6 && is_numeric(substr($npm, 4, 2))) ? substr($npm, 4, 2) : "10";

        $user = User::query()->updateOrCreate([
            "npm" => $npm,
        ], [
            "npm" => $npm,
            "email" => $user->getEmail(),
            "year" => $year,
            "major" => $major,
            "name" => $user->getName(),
            "google_id" => $user->getId(),
            "avatar" => $user->getAvatar(),
        ]);

        if ($user->wasRecentlyCreated) {
            $user->type = "voter";
            $user->save();
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect(route("index"));
    }

    public function logout(Request $request)
    {
        $request->session()->invalidate();
        Auth::logout();
        return redirect(route("login"));
    }
}
