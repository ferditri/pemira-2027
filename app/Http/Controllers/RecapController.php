<?php

namespace App\Http\Controllers;

use App\Models\Ballot;
use App\Models\BallotDetail;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class RecapController extends Controller
{
    public function index(Organization $organization)
    {
        return Inertia::render("recap/confirmation", [
            "organization" => $organization,
        ]);
    }

    public function reset(Request $request, Organization $organization)
    {
        if ($request->user()?->type !== "admin") {
            Log::warning("admin.recap.reset_denied", [
                "actor_id" => $request->user()?->id,
                "organization_id" => $organization->id,
            ]);

            return redirect()->route("organizations.dashboard", [
                "organization" => $organization,
            ])
                ->with("flash.message", "Hanya Administrator yang berhak mereset validasi rekap!")
                ->with("flash.type", "destructive");
        }

        $organization->ballots()
            ->where("is_confirmed", 1)
            ->update([
                "is_verified" => null,
                "verified_at" => null,
            ]);

        Log::warning("admin.recap.reset", [
            "actor_id" => $request->user()?->id,
            "organization_id" => $organization->id,
        ]);

        return redirect()->route("organizations.recap.validate", [
            "organization" => $organization->id,
        ]);
    }

    public function validate(Organization $organization)
    {
        $next = $organization->ballots()
            ->where("is_confirmed", 1)
            ->whereNotNull("confirmed_at")
            ->where("is_verified", null)
            ->where("verified_at", null)
            ->orderBy("confirmed_at", "asc")
            ->first();

        if ($next) {
            return Inertia::render("recap/validate", [
                "organization" => $organization,
                "ballot" => $next,
                "user" => $next->user,
                "verification" => $next->user->verification,
            ]);
        }

        return redirect()->route("organizations.recap.result", [
            "organization" => $organization->id,
        ]);
    }

    public function validateStore(Organization $organization, Request $request)
    {
        $request->validate([
            "ballot_id" => "required|exists:ballots,id",
            "verified" => "required|boolean",
        ]);

        $organization->ballots()
            ->where("is_confirmed", true)
            ->findOrFail($request->ballot_id)
            ->update([
                "is_verified" => $request->verified,
                "verified_at" => now(),
            ]);

        return redirect()->route("organizations.recap.validate", [
            "organization" => $organization->id,
        ]);
    }

    public function result(Organization $organization)
    {
        $groups = $organization->groups()
            ->with(['candidates' => function ($query) {
                $query->withCount(['ballotDetails as votes' => function ($q) {
                    $q->whereHas('ballot', fn($b) => $b->where('is_verified', true));
                }]);
            }])
            ->get();

        $groupsArray = [];
        foreach ($groups as $group) {
            $sortedCandidates = $group->candidates->sortByDesc("votes")->values()->all();
            $groupData = $group->toArray();
            $groupData["candidates"] = $sortedCandidates;
            $groupsArray[] = $groupData;
        }

        $ballots = $organization->ballots()->where("is_confirmed", true)
            ->count();
        $verified_ballots = $organization->ballots()
            ->where("is_confirmed", true)
            ->where("is_verified", true)
            ->count();

        return Inertia::render("recap/result", [
            "organization" => $organization,
            "groups" => $groupsArray,
            "ballots" => $ballots,
            "ballots_verified" => $verified_ballots,
        ]);
    }
}
