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
        return Inertia::render("whitelists/create");
    }

    /**
     * Store a newly created resource in storage.
     */
 public function store(Request $request)
{
    $request->validate([
        'whitelists' => 'required',
    ]);

    // 1. Bersihkan input: pecah baris, hilangkan spasi, buang baris yang kosong
    $rawNpms = preg_split("/\r\n|\n|\r/", $request->whitelists);
    $cleanNpms = array_filter(array_map('trim', $rawNpms));

    // 2. Eksekusi penghapusan data lama (Tetap gunakan logika asli Anda)
    // Whitelist::query()->whereDoesntHave('user')->delete();
    // Whitelist::query()->whereHas('user', function ($query) {
    //     $query->where('type', 'voter');
    // })->delete();

    // 3. Rakit data menjadi format Array Multidimensi
    $insertData = [];
    $now = now();
    foreach ($cleanNpms as $npm) {
        $insertData[] = [
            'npm' => $npm,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    // 4. BULK INSERT MAGIC: Masukkan per 1000 baris sekaligus
    foreach (array_chunk($insertData, 1000) as $chunk) {
        // insertOrIgnore akan mengabaikan data duplikat secara efisien
        Whitelist::insertOrIgnore($chunk);
    }

    return redirect(route("admin.whitelists.index"))
        ->with("flash.message", "Daftar pemilih massal berhasil diperbarui.")
        ->with("flash.type", "success");
}

    public function storeSingle(Request $request)
    {
        $request->validate(["npm" => "required|string"]);
        Whitelist::query()->firstOrCreate(["npm" => trim($request->npm)]);

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
