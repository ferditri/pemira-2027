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
        $organization->loadCount([
            "ballots" => fn ($q) => $q->where("is_confirmed", true),
        ]);
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
        Log::info("organization.voting.opened", [
            "actor_id" => $request->user()?->id,
            "actor_type" => $request->user()?->type,
            "organization_id" => $organization->id,
        ]);
        return redirect()->back()
            ->with("flash.message", "Pemilihan {$organization->name} berhasil dibuka!")
            ->with("flash.type", "success");
    }

    public function close(Organization $organization, Request $request)
    {
        $organization->update(["is_open" => false]);
        Log::info("organization.voting.closed", [
            "actor_id" => $request->user()?->id,
            "actor_type" => $request->user()?->type,
            "organization_id" => $organization->id,
        ]);
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
            $ballotsSnapshot = $organization->ballots()->get();

            if ($ballotsSnapshot->isNotEmpty()) {
                $timestamp = now()->format("Y-m-d_His");

                // Simpan rekap agregat & ringkasan partisipasi tanpa mengaitkan identitas pemilih ke pilihan kandidat
                $aggregateResults = $organization->groups()
                    ->with(["candidates" => function ($query) {
                        $query->withCount([
                            "ballotDetails as confirmed_votes" => fn ($q) => $q->whereHas("ballot", fn ($b) => $b->where("is_confirmed", true)),
                            "ballotDetails as verified_votes" => fn ($q) => $q->whereHas("ballot", fn ($b) => $b->where("is_confirmed", true)->where("is_verified", true)),
                        ]);
                    }])
                    ->get()
                    ->map(fn ($group) => [
                        "group_id" => $group->id,
                        "group_name" => $group->name,
                        "candidates" => $group->candidates->map(fn ($candidate) => [
                            "candidate_id" => $candidate->id,
                            "name_1" => $candidate->name_1,
                            "name_2" => $candidate->name_2,
                            "confirmed_votes" => $candidate->confirmed_votes,
                            "verified_votes" => $candidate->verified_votes,
                        ])->values()->all(),
                    ])->values()->all();

                $backupPayload = [
                    "reset_at" => now()->toIso8601String(),
                    "reset_by" => $request->user()?->only(["id", "npm", "name", "email"]),
                    "organization" => $organization->only(["id", "name", "major"]),
                    "ballots_count" => $ballotsSnapshot->count(),
                    "confirmed_ballots_count" => $ballotsSnapshot->where("is_confirmed", true)->count(),
                    "verified_ballots_count" => $ballotsSnapshot->where("is_confirmed", true)->where("is_verified", true)->count(),
                    "aggregate_results" => $aggregateResults,
                    "participation_summary" => $ballotsSnapshot->map(fn ($ballot) => [
                        "ballot_id" => $ballot->id,
                        "is_confirmed" => (bool) $ballot->is_confirmed,
                        "confirmed_at" => $ballot->confirmed_at,
                        "is_verified" => $ballot->is_verified,
                        "verified_at" => $ballot->verified_at,
                    ])->values()->all(),
                ];

                Storage::disk("local")->put(
                    "reset-backups/org_{$organization->id}_{$timestamp}.json",
                    json_encode($backupPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                );
            }

            $organization->ballotDetails()->delete();
            $organization->ballots()->update(["active_lock" => null]);
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
