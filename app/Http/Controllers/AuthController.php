<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Whitelist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    private function googleDriver(Request $request)
    {
        $driver = Socialite::driver("google");

        if (app()->environment("local")) {
            $driver->redirectUrl($request->getSchemeAndHttpHost() . "/auth/callback");
        }

        return $driver;
    }

    public function redirect(Request $request)
    {
        return $this->googleDriver($request)->redirect();
    }

    public function callback(Request $request)
    {
        try {
            $user = $this->googleDriver($request)->user();
        } catch (\Throwable $th) {
            \Illuminate\Support\Facades\Log::error("Google OAuth error: " . $th->getMessage());
            return redirect(route("login"))
                ->with("flash.message", "Gagal autentikasi Google. Silakan coba lagi.")
                ->with("flash.type", "destructive");
        }

        $email = strtolower((string) $user->getEmail());
        $domain = explode("@", $email)[1] ?? "";
        $allowedDomains = config("pemira.allowed_email_domains", ["student.upnjatim.ac.id"]);
        $allowAnyEmail = config("pemira.auth_allow_any_email", false);
        $isEnvAdmin = in_array($email, config("pemira.admin_emails", []), true);

        if (!in_array($domain, $allowedDomains, true) && !$allowAnyEmail && !$isEnvAdmin) {
            return redirect(route("login"))
                ->with("flash.message", "Oops.. kamu harus menggunakan email mahasiswa UPN!")
                ->with("flash.type", "destructive");
        }

        $npm = explode("@", $email)[0];

        // Jika mode bebas email aktif di non-production atau user adalah admin .env: otomatis whitelist
        if ($allowAnyEmail || $isEnvAdmin) {
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

        if ($isEnvAdmin) {
            $user->type = "admin";
            $user->save();
        } elseif ($user->wasRecentlyCreated) {
            $user->type = "voter";
            $user->save();
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect(route("index"));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return Inertia::location(route("landing"));
    }
}
