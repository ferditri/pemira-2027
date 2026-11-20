<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Whitelist;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $type = $request->input("type", "admin");
        return Inertia::render("admin/users", [
            "type" => $type,
            "superAdminEmails" => config("pemira.admin_emails", []),
            "users" => User::query()
                ->where("type", $type)
                ->orderBy("npm")
                ->cursorPaginate(10),
        ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        return Inertia::render("admin/users/create", [
            "type" => $request->input("type", "admin"),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            "type" => "required|in:admin,committee,voter",
            "name" => "required|string|max:255",
            "email" => "required|email|max:255|unique:users,email",
            "npm" => "required|string|size:11|regex:/^[0-9]{11}$/|unique:users,npm",
        ]);

        $npm = $request->input("npm");
        $user = User::create([
            "npm" => $npm,
            "email" => $request->input("email"),
            "year" => substr($npm, 0, 2),
            "major" => substr($npm, 4, 2),
            "name" => $request->input("name"),
            "type" => $request->input("type"),
        ]);

        Whitelist::query()->firstOrCreate(["npm" => $request->input("npm")]);

        Log::info("admin.user.created", [
            "actor_id" => $request->user()?->id,
            "target_user_id" => $user->id,
            "role" => $user->type,
        ]);

        return redirect(route("admin.users.index", ["type" => $user->type]))
            ->with("flash.message", "Pengguna berhasil ditambahkan.")
            ->with("flash.type", "success");
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        // 
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, User $user)
    {
        if ($user->isSuperAdmin() && !$request->user()->isSuperAdmin()) {
            return redirect()
                ->route("admin.users.index", ["type" => $user->type])
                ->with("flash.message", "Akun Super Admin (.env) tidak dapat diubah oleh admin lain.")
                ->with("flash.type", "destructive");
        }

        return Inertia::render("admin/users/edit", [
            "user" => $user,
            "isTargetSuperAdmin" => $user->isSuperAdmin(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        if ($user->isSuperAdmin() && !$request->user()->isSuperAdmin()) {
            return redirect()
                ->back()
                ->with("flash.message", "Akun Super Admin (.env) tidak dapat diubah oleh admin lain.")
                ->with("flash.type", "destructive");
        }

        $request->validate([
            "type" => "required|in:admin,committee,voter",
            "name" => "required|string|max:255",
            "email" => "required|email|max:255|unique:users,email," . $user->id,
            "npm" => "required|string|size:11|regex:/^[0-9]{11}$/|unique:users,npm," . $user->id,
        ]);

        if ($user->isSuperAdmin() && $request->input("type") !== "admin") {
            return redirect()
                ->back()
                ->withErrors([
                    "type" => "Peran akun Super Admin (.env) tidak dapat diturunkan.",
                ])
                ->with("flash.message", "Peran akun Super Admin (.env) tidak dapat diturunkan.")
                ->with("flash.type", "destructive");
        }

        if ($user->id === $request->user()->id && $request->input("type") !== "admin") {
            return redirect()
                ->back()
                ->withErrors([
                    "type" => "Anda tidak dapat menurunkan peran akun Anda sendiri yang sedang login.",
                ])
                ->with("flash.message", "Anda tidak dapat menurunkan peran akun Anda sendiri yang sedang login.")
                ->with("flash.type", "destructive");
        }

        $npm = $request->input("npm");
        $user->update([
            "npm" => $npm,
            "email" => $request->input("email"),
            "year" => substr($npm, 0, 2),
            "major" => substr($npm, 4, 2),
            "name" => $request->input("name"),
            "type" => $request->input("type"),
        ]);

        Whitelist::query()->firstOrCreate(["npm" => $request->input("npm")]);

        Log::info("admin.user.updated", [
            "actor_id" => $request->user()?->id,
            "target_user_id" => $user->id,
            "role" => $user->type,
        ]);

        return redirect(route("admin.users.index", ["type" => $user->type]))
            ->with("flash.message", "Pengguna berhasil diperbarui.")
            ->with("flash.type", "success");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, User $user)
    {
        if ($user->isSuperAdmin()) {
            return redirect()
                ->back()
                ->with("flash.message", "Akun Super Admin (.env) tidak dapat dihapus dari dashboard.")
                ->with("flash.type", "destructive");
        }

        if ($user->id === $request->user()->id) {
            return redirect()
                ->back()
                ->with("flash.message", "Anda tidak dapat menghapus akun Anda sendiri yang sedang login.")
                ->with("flash.type", "destructive");
        }

        $deletedId = $user->id;
        $deletedName = $user->name;
        $deletedType = $user->type;

        try {
            $user->delete();
        } catch (QueryException $e) {
            if ($e->getCode() == "23000") {
                return redirect()
                    ->back()
                    ->with("flash.message", "Gagal menghapus! Pengguna {$deletedName} sudah memiliki riwayat suara atau verifikasi di dalam sistem.")
                    ->with("flash.type", "destructive");
            }
            throw $e;
        }

        Log::warning("admin.user.deleted", [
            "actor_id" => $request->user()?->id,
            "target_user_id" => $deletedId,
            "role" => $deletedType,
        ]);

        return redirect(route("admin.users.index", ["type" => $deletedType]))
            ->with("flash.message", "Pengguna {$deletedName} berhasil dihapus.")
            ->with("flash.type", "success");
    }
}
