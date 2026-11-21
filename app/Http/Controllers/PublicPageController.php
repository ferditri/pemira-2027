<?php

namespace App\Http\Controllers;

use App\Models\LandingCandidate;
use Inertia\Inertia;

class PublicPageController extends Controller
{
    public function landing()
    {
        $candidates = LandingCandidate::query()
            ->orderBy("sort_order")
            ->orderBy("id")
            ->get();

        return view("landing", [
            "bemCandidates" => $candidates->where("section", "bem")->values(),
            "blmCandidates" => $candidates->where("section", "blm")->values(),
        ]);
    }

    public function privacyPolicy()
    {
        return view("privacy-policy");
    }

    public function login()
    {
        return Inertia::render("login");
    }
}
