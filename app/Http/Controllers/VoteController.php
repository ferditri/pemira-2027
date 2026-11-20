<?php

namespace App\Http\Controllers;

use App\Models\BallotDetail;
use App\Models\Group;
use App\Models\Organization;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class VoteController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $user->load("verification");

        $organizations = Organization::query()
            ->where(function (Builder $query) use ($user) {
                $query->where("major", null)
                    ->orWhere("major", $user->major);
            })
            ->where("is_open", true)
            ->get();

        return Inertia::render("vote/index", [
            "verification" => $user->verification,
            "organizations" => $organizations,
        ]);
    }

    public function organization(Request $request, Organization $organization)
    {
        if (!$organization->is_open) {
            return redirect()->route("index")
                ->with("flash.message", "Oops! Pemilihan untuk organisasi ini sedang ditutup 🔒")
                ->with("flash.type", "destructive");
        }

        $user = $request->user();
        $ballot = $user->ballot()
            ->where("organization_id", $organization->id)
            ->first();

        if ($ballot && $ballot->is_confirmed) {
            return redirect()->route("index")
                ->with("flash.message", "Oops! Kamu sudah menggunakan hak suaramu untuk organisasi ini 😅")
                ->with("flash.type", "destructive");
        }

        if (!$ballot) {
            $user->ballot()->create([
                "organization_id" => $organization->id,
            ]);
        }

        $first = $organization->firstGroup($user->year, $user->major);

        if (!$first) {
            return redirect()->route("index")
                ->with("flash.message", "Oops! sepertinya kamu tidak bisa ikut serta pemilihan organisasi ini 😅")
                ->with("flash.type", "destructive");
        }

        return redirect()->route("vote.group", [
            "organization" => $organization->id,
            "group" => $first->id
        ]);
    }

    public function group(
        Organization $organization,
        Group $group,
        Request $request
    ) {
        $user = $request->user();
        $ballot = $user->ballot()
            ->where("organization_id", $organization->id)
            ->first();
        $ballotDetails = $ballot->details()
            ->where("group_id", $group->id)
            ->get();

        return Inertia::render("vote/group", [
            "organization" => $organization,
            "group" => $group,
            "candidates" => $group->candidates,
            "ballotDetails" => $ballotDetails,
        ]);
    }

    public function groupPrevious(
        Organization $organization,
        Group $group,
        Request $request
    ) {
        $user = $request->user();
        $previousGroup = $group->prev($user->year, $user->major);
        if ($previousGroup) {
            return redirect()->route("vote.group", [
                "organization" => $organization->id,
                "group" => $previousGroup->id,
            ]);
        }

        return redirect()->route("index");
    }

    public function storeGroup(
        Request $request,
        Organization $organization,
        Group $group
    ) {
        if (!$organization->is_open) {
            return redirect()->route("index")
                ->with("flash.message", "Oops! Pemilihan untuk organisasi ini sedang ditutup 🔒")
                ->with("flash.type", "destructive");
        }

        $request->validate([
            'candidate_ids' => 'required|array',
            'candidate_ids.*' => 'required|integer|distinct|exists:candidates,id',
        ]);

        $groupCandidates = $group->candidates()->where("organization_id", $organization->id)->get();
        $validCandidateIds = $groupCandidates->pluck("id")->toArray();

        // Validasi kandidat harus merupakan kandidat resmi dari group & organisasi ini
        $invalidCandidates = array_diff($request->candidate_ids, $validCandidateIds);
        if (!empty($invalidCandidates)) {
            return redirect()
                ->back()
                ->withErrors([
                    "candidate_ids" => "Pilihan kandidat tidak valid untuk grup ini.",
                ])
                ->withInput();
        }

        $minCandidates = $group->min_candidates ?? 1;
        if (count($request->candidate_ids) < $minCandidates && $groupCandidates->count() >= $minCandidates) {
            return redirect()
                ->back()
                ->withErrors([
                    "candidate_ids" => "Pilih minimal {$minCandidates} kandidat",
                ])
                ->withInput();
        }

        if (count($request->candidate_ids) > $minCandidates) {
            return redirect()
                ->back()
                ->withErrors([
                    "candidate_ids" => "Maksimal pilih {$minCandidates} kandidat",
                ])
                ->withInput();
        }

        $user = $request->user();

        return DB::transaction(function () use ($user, $organization, $group, $request) {
            $ballot = $user->ballots()
                ->where("organization_id", $organization->id)
                ->lockForUpdate()
                ->first();

            if (!$ballot || $ballot->is_confirmed) {
                return redirect()->route("index")
                    ->with("flash.message", "Oops! Suara untuk organisasi ini sudah dikonfirmasi atau tidak valid 😅")
                    ->with("flash.type", "destructive");
            }

            $ballot->details()
                ->where("group_id", $group->id)
                ->delete();

            $uniqueCandidates = array_unique($request->candidate_ids);
            foreach ($uniqueCandidates as $candidateId) {
                $ballot->details()->create([
                    "organization_id" => $organization->id,
                    "group_id" => $group->id,
                    "candidate_id" => $candidateId,
                ]);
            }

            $nextGroup = $group->next($user->year, $user->major);

            if (!$nextGroup) {
                return redirect()->route("vote.result", [
                    "organization" => $organization->id,
                ]);
            }

            return redirect()->route("vote.group", [
                "organization" => $organization->id,
                "group" => $nextGroup,
            ]);
        });
    }

    public function result(Organization $organization, Request $request)
    {
        $user = $request->user();
        $ballot = $user->ballot()
            ->where("organization_id", $organization->id)
            ->first();

        if (!$ballot) {
            return redirect()->route("index");
        }

        if ($ballot->is_confirmed) {
            return redirect()->route("index")
                ->with("flash.message", "Oops! Kamu sudah menggunakan hak suaramu untuk organisasi ini 😅")
                ->with("flash.type", "destructive");
        }

        $details = $ballot->details()
            ->with("group", "candidate")
            ->get();

        return Inertia::render("vote/result", [
            "organization" => $organization,
            "details" => $details
        ]);
    }

    public function confirmResult(Request $request, Organization $organization)
    {
        if (!$organization->is_open) {
            return redirect()->route("index")
                ->with("flash.message", "Oops! Pemilihan untuk organisasi ini sedang ditutup 🔒")
                ->with("flash.type", "destructive");
        }

        $user = $request->user();

        return DB::transaction(function () use ($user, $organization) {
            $ballot = $user->ballots()
                ->where("organization_id", $organization->id)
                ->lockForUpdate()
                ->first();

            if (!$ballot || $ballot->is_confirmed) {
                return redirect()->route("index")
                    ->with("flash.message", "Oops! Suara kamu sudah tercatat sebelumnya 😅")
                    ->with("flash.type", "destructive");
            }

            if ($ballot->details()->count() === 0) {
                return redirect()->route("vote.organization", ["organization" => $organization->id])
                    ->with("flash.message", "Kamu belum memilih kandidat!")
                    ->with("flash.type", "destructive");
            }

            // Pastikan seluruh grup pemilihan yang berhak diikuti pemilih sudah lengkap terisi
            $eligibleGroups = $organization->eligibleGroups($user->year, $user->major);
            $eligibleGroups->loadCount("candidates");
            $detailsByGroup = $ballot->details()->get()->groupBy("group_id");

            foreach ($eligibleGroups as $eligibleGroup) {
                if ($eligibleGroup->candidates_count === 0) {
                    continue;
                }
                $required = min($eligibleGroup->min_candidates ?? 1, $eligibleGroup->candidates_count);
                $selectedCount = $detailsByGroup->get($eligibleGroup->id)?->count() ?? 0;
                if ($selectedCount < $required) {
                    return redirect()->route("vote.group", [
                        "organization" => $organization->id,
                        "group" => $eligibleGroup->id,
                    ])
                        ->with("flash.message", "Silakan lengkapi pilihan untuk {$eligibleGroup->name} terlebih dahulu!")
                        ->with("flash.type", "destructive");
                }
            }

            $updatePayload = [
                "is_confirmed" => 1,
                "confirmed_at" => now(),
            ];

            // Jika KTM pemilih sudah disetujui panitia sebelum voting, langsung tandai suara sah
            if ($user->verification?->status === "approved") {
                $updatePayload["is_verified"] = true;
                $updatePayload["verified_at"] = now();
            }

            $ballot->update($updatePayload);

            return redirect()
                ->route("index")
                ->with("flash.message", "Terima kasih telah berpartisipasi! 🤩");
        });
    }
}
