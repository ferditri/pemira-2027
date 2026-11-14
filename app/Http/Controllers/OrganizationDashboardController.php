<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class OrganizationDashboardController extends Controller
{
    public function dashboard(Organization $organization)
    {
        $organization->loadCount("ballots");
        $ballots = $organization->ballots()->with("user")
            ->where("is_confirmed", 1)
            ->cursorPaginate(5);

        return Inertia::render("organizations/dashboard", [
            "organization" => $organization,
            "ballots" => $ballots,
        ]);
    }

    public function open(Organization $organization, Request $request)
    {
        $organization->update(["is_open" => true]);
        return redirect()->back()
            ->with("flash.message", "Pemilihan {$organization->name} berhasil dibuka!")
            ->with("flash.type", "success");
    }

    public function close(Organization $organization)
    {
        $organization->update(["is_open" => false]);
        return redirect()->back()
            ->with("flash.message", "Pemilihan {$organization->name} berhasil ditutup!")
            ->with("flash.type", "success");
    }

    public function reset(Organization $organization, Request $request)
    {
        if ($request->user()->type !== 'admin') {
            Log::warning("admin.organization.reset_denied", [
                "actor_id" => $request->user()?->id,
                "organization_id" => $organization->id,
            ]);

            return redirect()->route("organizations.dashboard", [
                "organization" => $organization,
            ])
                ->with("flash.message", "Hanya Administrator yang berhak mereset data suara!")
                ->with("flash.type", "destructive");
        }

        DB::transaction(function () use ($organization, $request) {
            $ballotsSnapshot = $organization->ballots()
                ->with(["user:id,npm,name,email", "details"])
                ->get();

            if ($ballotsSnapshot->isNotEmpty()) {
                $timestamp = now()->format("Y-m-d_His");
                $backupPayload = [
                    "reset_at" => now()->toIso8601String(),
                    "reset_by" => $request->user()?->only(["id", "npm", "name", "email"]),
                    "organization" => $organization->only(["id", "name", "major"]),
                    "ballots_count" => $ballotsSnapshot->count(),
                    "ballots" => $ballotsSnapshot->toArray(),
                ];

                Storage::disk("local")->put(
                    "reset-backups/org_{$organization->id}_{$timestamp}.json",
                    json_encode($backupPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                );
            }

            $organization->ballotDetails()->delete();
            $organization->ballots()->delete();
        });

        Log::warning("admin.organization.reset", [
            "actor_id" => $request->user()?->id,
            "organization_id" => $organization->id,
        ]);

        return redirect()->route("organizations.dashboard", [
            "organization" => $organization,
        ])
            ->with("flash.message", "Data suara direset! Data lama tetap diarsipkan (soft-delete + backup JSON) dan tidak ikut dihitung.")
            ->with("flash.type", "success");
    }
}
