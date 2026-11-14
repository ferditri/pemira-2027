<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\LandingCandidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class LandingCandidateController extends Controller
{
    public function index()
    {
        $landingCandidates = LandingCandidate::query()
            ->orderBy('section')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $sourceCandidates = Candidate::query()
            ->with(['organization:id,name', 'group:id,name'])
            ->orderBy('organization_id')
            ->orderBy('group_id')
            ->orderBy('id')
            ->get();

        return Inertia::render('admin/landing-candidates', [
            'landingCandidates' => $landingCandidates,
            'sourceCandidates' => $sourceCandidates,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'section' => 'required|in:bem,blm',
            'candidate_id' => 'required|exists:candidates,id',
            'role_label' => 'required|string|max:120',
            'sort_order' => 'nullable|integer|min:0|max:999',
        ], [
            'candidate_id.required' => 'Silakan pilih calon dari menu Calon & Tipe terlebih dahulu.',
        ]);

        $candidate = Candidate::findOrFail($validated['candidate_id']);
        $nextOrder = $validated['sort_order'] ?? (
            (int) LandingCandidate::query()->where('section', $validated['section'])->max('sort_order') + 1
        );

        LandingCandidate::create([
            'section' => $validated['section'],
            'candidate_id' => $candidate->id,
            'role_label' => $validated['role_label'],
            'name' => $candidate->name_2 ? "{$candidate->name_1} & {$candidate->name_2}" : $candidate->name_1,
            'photo' => $candidate->picture,
            'vision' => $candidate->vision,
            'mission' => $this->parseMissionLines((string) $candidate->mission),
            'sort_order' => $nextOrder,
        ]);

        return redirect()
            ->route('admin.landing-candidates.index')
            ->with('flash.message', 'Kandidat landing page berhasil ditambahkan.')
            ->with('flash.type', 'success');
    }

    public function update(Request $request, LandingCandidate $landingCandidate)
    {
        $validated = $request->validate([
            'section' => 'required|in:bem,blm',
            'candidate_id' => 'required|exists:candidates,id',
            'role_label' => 'required|string|max:120',
            'sort_order' => 'nullable|integer|min:0|max:999',
        ], [
            'candidate_id.required' => 'Silakan pilih calon dari menu Calon & Tipe terlebih dahulu.',
        ]);

        $candidate = Candidate::findOrFail($validated['candidate_id']);

        $landingCandidate->update([
            'section' => $validated['section'],
            'candidate_id' => $candidate->id,
            'role_label' => $validated['role_label'],
            'name' => $candidate->name_2 ? "{$candidate->name_1} & {$candidate->name_2}" : $candidate->name_1,
            'photo' => $candidate->picture,
            'vision' => $candidate->vision,
            'mission' => $this->parseMissionLines((string) $candidate->mission),
            'sort_order' => $validated['sort_order'] ?? $landingCandidate->sort_order,
        ]);

        return redirect()
            ->route('admin.landing-candidates.index')
            ->with('flash.message', 'Kandidat landing page berhasil diperbarui.')
            ->with('flash.type', 'success');
    }

    public function destroy(LandingCandidate $landingCandidate)
    {
        $this->deleteManagedPhoto($landingCandidate->photo);
        $landingCandidate->delete();

        return redirect()
            ->route('admin.landing-candidates.index')
            ->with('flash.message', 'Kandidat landing page berhasil dihapus.')
            ->with('flash.type', 'success');
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:landing_candidates,id',
        ]);

        foreach (array_values($validated['ids']) as $index => $id) {
            LandingCandidate::where('id', $id)->update(['sort_order' => $index + 1]);
        }

        return redirect()
            ->route('admin.landing-candidates.index')
            ->with('flash.message', 'Urutan kandidat landing berhasil diperbarui.')
            ->with('flash.type', 'success');
    }

    private function parseMissionLines(string $raw): array
    {
        $lines = preg_split('/\r\n|\n|\r/', $raw) ?: [];
        $cleaned = array_values(array_filter(array_map('trim', $lines), fn ($line) => $line !== ''));

        return $cleaned ?: [$raw];
    }

    private function deleteManagedPhoto(?string $photo): void
    {
        if (!$photo || !str_starts_with($photo, '/storage/landing-candidates/')) {
            return;
        }

        $relativePath = str_replace('/storage/', '', $photo);
        if (Storage::disk('public')->exists($relativePath)) {
            Storage::disk('public')->delete($relativePath);
        }
    }
}
