<?php

namespace App\Http\Controllers\Master\Pengaturan;

use App\Http\Controllers\Controller;
use App\Models\Akademik\ProgramStudi;
use App\Models\Dosen;
use App\Models\Jabatan;
use App\Models\Pengaturan\WebSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class JabatanController extends Controller
{
    private function pageData()
    {
        $user = Auth::user();
        $webs = WebSetting::first();

        return [
            'user' => $user,
            'webs' => $webs,
            'spref' => $user?->prefix ?? '',
            'menus' => 'Pengaturan',
            'pages' => 'Jabatan',
            'academy' => ($webs?->school_apps ?? 'SIAKAD') . ' by ' . ($webs?->school_name ?? 'STIT Darul Ilmi Tasikmalaya'),
        ];
    }

    public function renderJabatan()
    {
        $data = $this->pageData();
        $data['jabatan'] = Jabatan::with(['dosen', 'programStudi'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
        $data['dosen'] = Dosen::where('type', 1)->orderBy('name')->get();
        $data['program_studi'] = ProgramStudi::where('status', 'Aktif')->orderBy('name')->get();

        return view('master.pengaturan.jabatan-index', $data);
    }

    public function handleJabatan(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'category' => 'nullable|string|max:50',
            'dosen_id' => 'nullable|exists:dosens,id',
            'prodi_id' => 'nullable|exists:program_studis,id',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0|max:9999',
            'is_active' => 'nullable|boolean',
        ]);

        Jabatan::create([
            'code' => 'JBT-' . Str::upper(Str::random(8)),
            'name' => trim($validated['name']),
            'category' => $validated['category'] ?? null,
            'dosen_id' => $validated['dosen_id'] ?? null,
            'prodi_id' => $validated['prodi_id'] ?? null,
            'description' => $validated['description'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->back()->with('success', 'Jabatan berhasil ditambahkan.');
    }

    public function updateJabatan(Request $request, string $code)
    {
        $jabatan = Jabatan::where('code', $code)->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'category' => 'nullable|string|max:50',
            'dosen_id' => 'nullable|exists:dosens,id',
            'prodi_id' => 'nullable|exists:program_studis,id',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0|max:9999',
            'is_active' => nullable|boolean',
        ]);

        $jabatan->update([
            'name' => trim($validated['name']),
            'category' => $validated['category'] ?? null,
            'dosen_id' => $validated['dosen_id'] ?? null,
            'prodi_id' => $validated['prodi_id'] ?? null,
            'description' => $validated['description'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->back()->with('success', 'Jabatan berhasil diperbarui.');
    }

    public function deleteJabatan(string $code)
    {
        $jabatan = Jabatan::where('code', $code)->firstOrFail();
        $jabatan->delete();

        return redirect()->back()->with('success', 'Jabatan berhasil dihapus.');
    }
}
