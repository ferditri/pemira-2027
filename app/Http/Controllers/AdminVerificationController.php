<?php

namespace App\Http\Controllers;

use App\Models\Ballot;
use App\Models\UserVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class AdminVerificationController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->string('status', 'all')->toString();
        $search = trim($request->string('search', '')->toString());

        $baseQuery = UserVerification::query()->where('is_active', true);

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
            'approved' => (clone $baseQuery)->where('status', 'approved')->count(),
            'retake' => (clone $baseQuery)->where('status', 'retake')->count(),
            'rejected' => (clone $baseQuery)->where('status', 'rejected')->count(),
        ];

        $query = UserVerification::query()
            ->where('is_active', true)
            ->with([
                'user:id,npm,name,email,major,year',
                'reviewer:id,name,npm',
                'user.verificationHistories' => fn ($q) => $q->with('reviewer:id,name')->orderByDesc('created_at'),
            ])
            ->orderByRaw("CASE status WHEN 'pending' THEN 0 WHEN 'retake' THEN 1 WHEN 'rejected' THEN 2 ELSE 3 END")
            ->orderByDesc('created_at');

        if (in_array($status, ['pending', 'approved', 'retake', 'rejected'], true)) {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('npm', 'like', "%{$search}%");
            });
        }

        $verifications = $query->get();

        return Inertia::render('admin/verifications', [
            'verifications' => $verifications,
            'stats' => $stats,
            'exampleImages' => UserVerification::exampleUrls(),
            'filters' => [
                'status' => $status,
                'search' => $search,
            ],
        ]);
    }

    public function review(Request $request, UserVerification $verification)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected,retake',
            'admin_note' => 'nullable|string|max:500',
        ]);

        $verification->update([
            'status' => $validated['status'],
            'admin_note' => $validated['admin_note'] ?? null,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        // Sinkronkan status validasi dengan surat suara (ballots) milik user ini:
        // Jika Sah -> ballot confirmed dianggap sah; jika Tidak Sah / Re-take -> tidak dihitung.
        $isBallotVerified = $validated['status'] === 'approved';
        Ballot::query()
            ->where('user_id', $verification->user_id)
            ->where('is_confirmed', true)
            ->update([
                'is_verified' => $isBallotVerified,
                'verified_at' => now(),
            ]);

        $label = match ($validated['status']) {
            'approved' => 'disetujui (Sah)',
            'retake' => 'diminta Re-take',
            'rejected' => 'ditolak (Tidak Sah — suara tidak dihitung)',
        };

        return redirect()->back()
            ->with('flash.message', "Verifikasi {$verification->user->name} berhasil {$label}.")
            ->with('flash.type', 'success');
    }

    public function archive(UserVerification $verification)
    {
        $verification->update([
            'is_active' => false,
        ]);

        return redirect()->back()
            ->with('flash.message', "Verifikasi {$verification->user->name} diarsipkan dari daftar aktif (riwayat tetap tersimpan).")
            ->with('flash.type', 'success');
    }

    public function updateExamples(Request $request)
    {
        $request->validate([
            'ktm' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'selfie' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'reset_ktm' => 'nullable|boolean',
            'reset_selfie' => 'nullable|boolean',
        ]);

        $disk = Storage::disk('public');

        if ($request->boolean('reset_ktm') || $request->hasFile('ktm')) {
            foreach ($disk->files('examples') as $file) {
                if (str_starts_with(basename($file), 'ktm.')) {
                    $disk->delete($file);
                }
            }
            if ($request->hasFile('ktm')) {
                $ext = $request->file('ktm')->getClientOriginalExtension() ?: 'jpg';
                $request->file('ktm')->storeAs('examples', 'ktm.' . $ext, 'public');
            }
        }

        if ($request->boolean('reset_selfie') || $request->hasFile('selfie')) {
            foreach ($disk->files('examples') as $file) {
                if (str_starts_with(basename($file), 'selfie.')) {
                    $disk->delete($file);
                }
            }
            if ($request->hasFile('selfie')) {
                $ext = $request->file('selfie')->getClientOriginalExtension() ?: 'jpg';
                $request->file('selfie')->storeAs('examples', 'selfie.' . $ext, 'public');
            }
        }

        return redirect()->back()
            ->with('flash.message', 'Foto contoh verifikasi berhasil diperbarui.')
            ->with('flash.type', 'success');
    }
}
