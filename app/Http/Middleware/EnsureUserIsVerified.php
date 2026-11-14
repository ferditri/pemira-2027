<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $verification = $user->verification;

        if (!$verification || in_array($verification->status, ["retake", "rejected"], true)) {
            return redirect()->route("index")
                ->with("flash.message", "Silakan selesaikan atau ulangi verifikasi KTM & selfie terlebih dahulu.")
                ->with("flash.type", "destructive");
        }

        return $next($request);
    }
}
