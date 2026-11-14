<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class OrganizationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render("admin/organizations", [
            "organizations" => Organization::all(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render("admin/organizations/create");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'major' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $logoPath = '';
        if ($request->hasFile('logo')) {
            $newPath = $request->file('logo')->store('organizations', 'public');
            $logoPath = '/storage/' . $newPath;
        }

        $organization = Organization::create([
            'name' => $validated['name'],
            'major' => $validated['major'] ?: null,
            'logo' => $logoPath,
            'ordering' => Organization::count() + 1,
            'is_open' => false,
        ]);

        return redirect()
            ->route('admin.candidates.index')
            ->with('flash.message', 'Organization successfully created!')
            ->with('flash.type', 'success');
    }

    /**
     * Display the specified resource.
     */
    public function show(Organization $organization)
    {
        return Inertia::render("admin/organizations/show", [
            "organization" => $organization,
            "groups" => $organization->groups()->withCount("candidates")->get(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Organization $organization)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Organization $organization)
    {
       // 1. Validasi Data yang masuk (berdasarkan kolom di UI Anda)
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'major' => 'nullable|string|max:255', // UI menyatakan Major itu opsional
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $logoPath = $organization->logo; // Ambil path logo lama sebagai default

        // 2. Jika ada file logo baru yang diunggah
        if ($request->hasFile('logo')) {
            // Bersihkan format path '/storage/...' agar bisa dihapus oleh sistem
            $oldFilePath = str_replace('/storage/', '', $logoPath);
            
            // Hapus logo lama dari server
            if ($logoPath && Storage::disk('public')->exists($oldFilePath)) {
               Storage::disk('public')->delete($oldFilePath);
            }
            
            // Simpan logo baru
            $newPath = $request->file('logo')->store('organizations', 'public');
            $logoPath = '/storage/' . $newPath;
        }

        // 3. Simpan perubahan ke database
        $organization->update([
            'name' => $validated['name'],
            'major' => $validated['major'],
            'logo' => $logoPath,
        ]);

        // 4. Redirect kembali ke Calon & Tipe dengan Flash Message
        return redirect()->route('admin.candidates.index')
            ->with('flash.message', 'Organization successfully updated!')
            ->with('flash.type', 'success');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Organization $organization)
    {
        try {
            $logoPath = $organization->logo;
            $organization->delete();

            if ($logoPath) {
                $oldFilePath = str_replace('/storage/', '', $logoPath);
                if (Storage::disk('public')->exists($oldFilePath)) {
                    Storage::disk('public')->delete($oldFilePath);
                }
            }

            return redirect()
                ->route('admin.candidates.index')
                ->with('flash.message', "Organisasi {$organization->name} berhasil dihapus!")
                ->with('flash.type', 'success');
        } catch (QueryException $e) {
            if ($e->getCode() == '23000') {
                return redirect()
                    ->back()
                    ->with('flash.message', 'Gagal menghapus! Hapus semua Tipe/Grup & data suara di organisasi ini terlebih dahulu.')
                    ->with('flash.type', 'destructive');
            }

            throw $e;
        }
    }
}
