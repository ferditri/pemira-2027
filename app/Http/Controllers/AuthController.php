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
            $googleUser = $this->googleDriver($request)->user();
        } catch (\Throwable $th) {
            Log::error("Google OAuth error: " . $th->getMessage());
            return redirect(route("login"))
                ->with("flash.message", "Gagal autentikasi Google. Silakan coba lagi.")
                ->with("flash.type", "destructive");
        }

        $rawGoogleProfile = is_array($googleUser->user ?? null) ? $googleUser->user : [];
        if (array_key_exists("email_verified", $rawGoogleProfile) && !$rawGoogleProfile["email_verified"]) {
            Log::warning("auth.unverified_google_email", [
                "google_id" => $googleUser->getId(),
            ]);
            return redirect(route("login"))
                ->with("flash.message", "Akun Google kamu belum terverifikasi emailnya.")
                ->with("flash.type", "destructive");
        }

        $email = strtolower(trim((string) $googleUser->getEmail()));
        $googleId = (string) $googleUser->getId();
        $domain = explode("@", $email)[1] ?? "";
        $allowedDomains = config("pemira.allowed_email_domains", ["student.upnjatim.ac.id"]);
        $allowAnyEmail = config("pemira.auth_allow_any_email", false);
        $isEnvAdmin = in_array($email, config("pemira.admin_emails", []), true);

        if (!in_array($domain, $allowedDomains, true) && !$allowAnyEmail && !$isEnvAdmin) {
            return redirect(route("login"))
                ->with("flash.message", "Oops.. kamu harus menggunakan email mahasiswa UPN!")
                ->with("flash.type", "destructive");
        }

        $npm = substr(explode("@", $email)[0], 0, 64);

        // Cocokkan akun berdasarkan email (case-insensitive) terlebih dahulu agar prefix npm tidak menimpa akun lain
        $existingUser = User::query()
            ->whereRaw("LOWER(email) = ?", [$email])
            ->first();

        if ($existingUser) {
            if ($existingUser->google_id !== null && $existingUser->google_id !== $googleId) {
                Log::warning("auth.google_id_mismatch", [
                    "user_id" => $existingUser->id,
                    "email" => $email,
                ]);
                return redirect(route("login"))
                    ->with("flash.message", "Akun ini sudah terhubung dengan akun Google berbeda. Hubungi panitia.")
                    ->with("flash.type", "destructive");
            }

            if ($allowAnyEmail || $isEnvAdmin) {
                Whitelist::query()->firstOrCreate(["npm" => $existingUser->npm]);
            }

            if (Whitelist::query()->find($existingUser->npm) === null && !$isEnvAdmin) {
                return redirect(route("login"))
                    ->with("flash.message", "Maaf.. NPM kamu tidak terdaftar sebagai pemilih 😔")
                    ->with("flash.type", "destructive");
            }

            $existingUser->fill([
                "email" => $email,
                "name" => $googleUser->getName(),
                "google_id" => $googleId,
                "avatar" => $googleUser->getAvatar(),
            ]);

            if ($isEnvAdmin) {
                $existingUser->type = "admin";
            }

            $existingUser->save();
            $user = $existingUser;
        } else {
            // Cegah kolisi NPM: jika NPM sudah dipakai user lain dengan email berbeda, tolak login
            $npmCollision = User::query()->where("npm", $npm)->first();
            if ($npmCollision !== null) {
                Log::warning("auth.npm_collision_blocked", [
                    "npm" => $npm,
                    "incoming_email" => $email,
                    "existing_user_id" => $npmCollision->id,
                ]);
                return redirect(route("login"))
                    ->with("flash.message", "Akun dengan NPM tersebut sudah terdaftar dengan email berbeda. Hubungi panitia.")
                    ->with("flash.type", "destructive");
            }

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

            $user = User::query()->create([
                "npm" => $npm,
                "email" => $email,
                "year" => $year,
                "major" => $major,
                "name" => $googleUser->getName(),
                "google_id" => $googleId,
                "avatar" => $googleUser->getAvatar(),
                "type" => $isEnvAdmin ? "admin" : "voter",
            ]);
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
