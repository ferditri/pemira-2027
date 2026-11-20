<?php

use App\Http\Controllers\AdminCandidateController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminElectionController;
use App\Http\Controllers\AdminVerificationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\LandingCandidateController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\OrganizationDashboardController;
use App\Http\Controllers\RecapController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserVerificationController;
use App\Http\Controllers\VoteController;
use App\Http\Controllers\WhitelistController;
use App\Models\LandingCandidate;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\ComitteeMiddleware;
use App\Http\Middleware\EnsureGroupBelongsToOrganization;
use App\Http\Middleware\EnsureUserCanVote;
use App\Http\Middleware\EnsureUserCanVoteGroup;
use App\Http\Middleware\EnsureUserHasBallot;
use App\Http\Middleware\EnsureUserIsVerified;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

if (app()->environment("local")) {
    Route::get("/dev/login/{npm}", function ($npm) {
        $user = User::where("npm", $npm)->firstOrFail();
        Auth::login($user);
        return redirect()->route("index")->with("flash.message", "Logged in as {$user->name} ({$user->type})");
    })->name("dev.login");
}

Route::get("/", function () {
    $candidates = LandingCandidate::query()
        ->orderBy("sort_order")
        ->orderBy("id")
        ->get();

    return view("landing", [
        "bemCandidates" => $candidates->where("section", "bem")->values(),
        "blmCandidates" => $candidates->where("section", "blm")->values(),
    ]);
})->name("landing");

Route::get("/privacy-policy", function () {
    return view("privacy-policy");
})->name("privacy-policy");

Route::middleware("guest")->group(function () {
    Route::get("/login", fn() => Inertia::render("login"))->name("login");
    Route::get("/auth/redirect", [AuthController::class, "redirect"])
        ->middleware("throttle:20,1")
        ->name("auth.redirect");
    Route::get("/auth/callback", [AuthController::class, "callback"])
        ->middleware("throttle:20,1")
        ->name("auth.callback");
});

Route::middleware("auth")->group(function () {
    Route::get("welcome", [VoteController::class, "index"])->name("index");

    Route::get("/verification", [UserVerificationController::class, "index"])
        ->name("verification");
    Route::post("/verification", [UserVerificationController::class, "store"])
        ->middleware("throttle:15,1")
        ->name("verification.store");
    Route::get("/verifications/{verification}/media/{type}", [AdminVerificationController::class, "media"])
        ->whereIn("type", ["ktm", "verification"])
        ->name("verifications.media");

    Route::post("/logout", [AuthController::class, "logout"])->name("logout");

    Route::prefix("admin")->as("admin.")->middleware(AdminMiddleware::class)->group(function () {
        Route::get("/", [AdminDashboardController::class, "index"])
            ->name("dashboard");

        Route::get("/verifications", [AdminVerificationController::class, "index"])
            ->name("verifications.index");
        Route::post("/verifications/examples", [AdminVerificationController::class, "updateExamples"])
            ->name("verifications.examples");
        Route::post("/verifications/{verification}/review", [AdminVerificationController::class, "review"])
            ->name("verifications.review");
        Route::post("/verifications/{verification}/archive", [AdminVerificationController::class, "archive"])
            ->name("verifications.archive");

        Route::get("/elections", [AdminElectionController::class, "index"])
            ->name("elections.index");

        Route::get("/candidates", [AdminCandidateController::class, "index"])
            ->name("candidates.index");

        Route::resource("whitelists", WhitelistController::class);
        Route::post("/whitelists/validate", [WhitelistController::class, "validate"])
            ->name("whitelists.validate");
        Route::post("/whitelists/single", [WhitelistController::class, "storeSingle"])
            ->name("whitelists.store.single");

        Route::resource("users", UserController::class);
        Route::post("landing-candidates/reorder", [LandingCandidateController::class, "reorder"])
            ->name("landing-candidates.reorder");
        Route::resource("landing-candidates", LandingCandidateController::class)
            ->only(["index", "store", "update", "destroy"]);
        Route::resource("organizations", OrganizationController::class);
        Route::resource("organizations.groups", GroupController::class);
        Route::resource(
            "organizations.groups.candidates",
            CandidateController::class
        );
    });

    Route::prefix("organizations/{organization}")->middleware(ComitteeMiddleware::class)->group(function () {
        Route::get("/", [OrganizationDashboardController::class, "dashboard"])
            ->name("organizations.dashboard");
        Route::post("/open", [OrganizationDashboardController::class, "open"])
            ->name("organizations.open");
        Route::post("/close", [OrganizationDashboardController::class, "close"])
            ->name("organizations.close");
        Route::post("/reset", [OrganizationDashboardController::class, "reset"])
            ->name("organizations.reset");

        Route::prefix("/recap")->group(function () {
            Route::get("/", [RecapController::class, "index"])
                ->name("organizations.recap.confirmation");
            Route::get("/validate", [RecapController::class, "validate"])
                ->name("organizations.recap.validate");
            Route::post("/reset", [RecapController::class, "reset"])
                ->name("organizations.recap.reset");
            Route::post("/validate", [RecapController::class, "validateStore"])
                ->name("organizations.recap.validate.store");
            Route::get("/result", [RecapController::class, "result"])
                ->name("organizations.recap.result");
        });
    });

    Route::prefix("/vote/{organization}")
        ->middleware([
            EnsureUserCanVote::class,
            EnsureUserIsVerified::class,
        ])
        ->group(function () {
            Route::get("/", [VoteController::class, "organization"])
                ->name("vote.organization");

            Route::middleware([
                EnsureUserHasBallot::class,
            ])->group(function () {
                Route::get("/result", [VoteController::class, "result"])
                    ->name("vote.result");
                Route::post("/result", [VoteController::class, "confirmResult"])
                    ->middleware("throttle:15,1")
                    ->name("vote.result.confirm");

                Route::middleware([
                    EnsureGroupBelongsToOrganization::class,
                    EnsureUserCanVoteGroup::class,
                ])->group(function () {
                    Route::get("/{group}", [VoteController::class, "group"])
                        ->name("vote.group");
                    Route::get("/{group}/previous", [VoteController::class, "groupPrevious"])
                        ->name("vote.group.previous");
                    Route::post("/{group}", [VoteController::class, "storeGroup"])
                        ->middleware("throttle:20,1")
                        ->name("vote.group.store");
                });
            });
        });
});

