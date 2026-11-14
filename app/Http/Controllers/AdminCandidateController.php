<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Inertia\Inertia;

class AdminCandidateController extends Controller
{
    public function index()
    {
        $organizations = Organization::query()
            ->orderBy('ordering')
            ->with([
                'groups' => fn ($q) => $q->orderBy('ordering')->with('candidates'),
            ])
            ->get();

        return Inertia::render('admin/candidates', [
            'organizations' => $organizations,
        ]);
    }
}
