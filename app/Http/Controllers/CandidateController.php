<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Group;
use App\Models\Organization;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CandidateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Organization $organization, Group $group)
    {
       return redirect()->route('admin.organizations.groups.show', [
            'organization' => $organization->id,
            'group' => $group->id
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render("admin/organizations/groups/candidates/create");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Organization $organization, Group $group)
    {
     // 1. Validasi Data yang Masuk
        $validated = $request->validate([
            'name_1' => 'required|string|max:255',
            'name_2' => 'nullable|string|max:255',
            'vision' => 'required|string',
            'mission' => 'required|string',
            'picture' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048', // Maks 2MB
        ]);

        // 2. Proses Upload Gambar
        $picturePath = null;
        if ($request->hasFile('picture')) {
            // Simpan gambar ke folder 'storage/app/public/candidates'
            $picturePath = $request->file('picture')->store('candidates', 'public');
        }

        // 3. Simpan ke Database melalui relasi Group
        $group->candidates()->create([
            'organization_id' => $organization->id, // Wajib diisi sesuai skema database
            'name_1' => $validated['name_1'],
            'name_2' => $validated['name_2'],
            'vision' => $validated['vision'],
            'mission' => $validated['mission'],
            'picture' => $picturePath ? '/storage/' . $picturePath : null, // Format path untuk Vue
        ]);

        $redirect = $request->query('from') === 'group'
            ? redirect()->route('admin.organizations.groups.show', [
                'organization' => $organization->id,
                'group' => $group->id,
            ])
            : redirect()->route('admin.candidates.index');

        return $redirect
            ->with('flash.message', 'Calon berhasil ditambahkan!')
            ->with('flash.type', 'success');
    }

    /**
     * Display the specified resource.
     */
    public function show(
        Organization $organization,
        Group $group,
        Candidate $candidate
    ) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(
        Organization $organization,
        Group $group,
        Candidate $candidate
    ) {
        return Inertia::render("admin/organizations/groups/candidates/edit", [
        'organization' => $organization,
        'group' => $group,    
        "candidate" => $candidate,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        Request $request,
        Organization $organization,
        Group $group,
        Candidate $candidate
    ) {
       // 1. Validasi Data
        $validated = $request->validate([
            'name_1' => 'required|string|max:255',
            'name_2' => 'nullable|string|max:255',
            'vision' => 'required|string',
            'mission' => 'required|string',
            'picture' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', 
        ]);

        $picturePath = $candidate->picture; // Ambil path foto lama sebagai default

        // 2. Cek apakah ada file foto baru yang diunggah
        if ($request->hasFile('picture')) {
            // Hapus foto lama dari server untuk menghemat ruang (hanya folder candidates/)
            if ($picturePath && str_starts_with($picturePath, '/storage/candidates/')) {
                $oldFilePath = str_replace('/storage/', '', $picturePath);
                if (Storage::disk('public')->exists($oldFilePath)) {
                    Storage::disk('public')->delete($oldFilePath);
                }
            }
            
            // Simpan foto baru
            $newPath = $request->file('picture')->store('candidates', 'public');
            $picturePath = '/storage/' . $newPath;
        }

        // 3. EKSEKUSI PENYIMPANAN KE DATABASE (Ini yang sebelumnya hilang)
        $candidate->update([
            'organization_id' => $organization->id,
            'group_id' => $group->id,
            'name_1' => $validated['name_1'],
            'name_2' => $validated['name_2'],
            'vision' => $validated['vision'],
            'mission' => $validated['mission'],
            'picture' => $picturePath,
        ]);

        $redirect = $request->query('from') === 'group'
            ? redirect()->route('admin.organizations.groups.show', [
                'organization' => $organization->id,
                'group' => $group->id,
            ])
            : redirect()->route('admin.candidates.index');

        return $redirect
            ->with('flash.message', 'Data calon berhasil diperbarui!')
            ->with('flash.type', 'success');
        }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(
        Request $request,
        Organization $organization,
        Group $group,
        Candidate $candidate
    ) {
        $redirectRoute = $request->query('from') === 'group'
            ? route('admin.organizations.groups.show', [
                'organization' => $organization->id,
                'group' => $group->id,
            ])
            : route('admin.candidates.index');

        try {
            // 1. Coba hapus data dari database terlebih dahulu
            $candidate->delete();

            // 2. JIKA BERHASIL (tidak ditolak MySQL), baru hapus foto fisik dari Storage (hanya folder candidates/)
            if ($candidate->picture && str_starts_with($candidate->picture, '/storage/candidates/')) {
                $oldFilePath = str_replace('/storage/', '', $candidate->picture);
                if (Storage::disk('public')->exists($oldFilePath)) {
                    Storage::disk('public')->delete($oldFilePath);
                }
            }

            // 3. Kembalikan dengan pesan sukses
            return redirect($redirectRoute)
                ->with('flash.message', 'Calon berhasil dihapus!')
                ->with('flash.type', 'success');

        } catch (QueryException $e) {
            // 4. TANGKAP ERROR MySQL: Jika kode error adalah 23000 (Integrity Constraint Violation)
            if ($e->getCode() == "23000") {
                return redirect($redirectRoute)
                    ->with('flash.message', 'Gagal menghapus! Kandidat ini sudah mendapatkan suara (Vote) di dalam sistem. Bersihkan data suara terlebih dahulu.')
                    ->with('flash.type', 'destructive');
            }

            // Jika error disebabkan oleh hal lain, lempar kembali agar terlihat di log
            throw $e;
        }
    }
}
