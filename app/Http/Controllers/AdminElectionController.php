<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Inertia\Inertia;

class AdminElectionController extends Controller
{
    public function index()
    {
        $organizations = Organization::query()
            ->orderBy('ordering')
            ->withCount([
                'groups',
                'candidates',
                'ballots as ballots_count' => fn ($q) => $q->where('is_confirmed', true),
                'ballots as verified_ballots_count' => fn ($q) => $q->where('is_confirmed', true)->where('is_verified', true),
            ])
            ->get();

        return Inertia::render('admin/elections', [
            'organizations' => $organizations,
        ]);
    }
}
