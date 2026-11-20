<?php

namespace App\Http\Controllers;

use App\Models\Whitelist;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WhitelistController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->query("search", ""));

        $whitelists = Whitelist::query()
            ->with("user:id,npm,name,type")
            ->when($search !== "", function ($query) use ($search) {
                $query->where("npm", "like", "%" . $search . "%");
            })
            ->orderByDesc("created_at")
            ->orderBy("npm")
            ->paginate(15)
            ->withQueryString();

        return Inertia::render("admin/whitelists", [
            "whitelists" => $whitelists,
            "search" => $search,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return redirect()->route("admin.whitelists.index");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'whitelists' => 'required|string|max:500000',
        ]);

        // 1. Bersihkan input: pecah baris, hilangkan spasi, buang baris kosong, dan deduplikasi
        $rawNpms = preg_split("/\r\n|\n|\r/", (string) $request->whitelists) ?: [];
        $trimmedNpms = array_values(array_unique(array_filter(array_map('trim', $rawNpms), fn ($v) => $v !== '')));

        $validNpms = [];
        $skippedCount = 0;
        foreach ($trimmedNpms as $npm) {
            if (preg_match('/^[A-Za-z0-9._-]{3,50}$/', $npm)) {
                $validNpms[] = $npm;
            } else {
                $skippedCount++;
            }
        }

        if (empty($validNpms)) {
            return redirect()
                ->back()
                ->withErrors(['whitelists' => 'Tidak ada NPM valid yang ditemukan. Pastikan tiap baris hanya berisi NPM.']);
        }

        // 2. Rakit data menjadi format Array Multidimensi
        $insertData = [];
        $now = now();
        foreach ($validNpms as $npm) {
            $insertData[] = [
                'npm' => $npm,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // 3. BULK INSERT per 1000 baris sekaligus
        foreach (array_chunk($insertData, 1000) as $chunk) {
            Whitelist::insertOrIgnore($chunk);
        }

        $msg = count($validNpms) . ' NPM valid diproses ke daftar pemilih.';
        if ($skippedCount > 0) {
            $msg .= " ({$skippedCount} baris tidak valid dilewati.)";
        }

        return redirect(route("admin.whitelists.index"))
            ->with("flash.message", $msg)
            ->with("flash.type", "success");
    }

    public function storeSingle(Request $request)
    {
        $validated = $request->validate([
            "npm" => ["required", "string", "min:3", "max:50", "regex:/^[A-Za-z0-9._-]+$/"],
        ], [
            "npm.regex" => "Format NPM tidak valid (hanya huruf, angka, titik, garis bawah, atau strip).",
        ]);

        Whitelist::query()->firstOrCreate(["npm" => trim($validated["npm"])]);

        return redirect()->back()
            ->with("flash.message", "NPM berhasil ditambahkan ke daftar pemilih.")
            ->with("flash.type", "success");
    }

    /**
     * Display the specified resource.
     */
    public function show(string $whitelist)
    {
        Whitelist::findOrFail($whitelist);
    }

    public function validate(Request $request)
    {
        $request->validate(["npm" => "required|exists:whitelists,npm"]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Whitelist $whitelist)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Whitelist $whitelist)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Whitelist $whitelist)
    {
        $npm = $whitelist->npm;
        $whitelist->delete();

        return redirect()->back()
            ->with("flash.message", "NPM {$npm} berhasil dihapus dari daftar pemilih.")
            ->with("flash.type", "success");
    }
}
