<?php

namespace App\Http\Controllers\Master\Akademik;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// Use System
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MataKuliahExport;
use App\Exports\MataKuliahFullExport;
use App\Exports\MataKuliahImportTemplate;
use App\Imports\MataKuliahImport;
// Use Models
use App\Models\Akademik\MataKuliah;
use App\Models\Akademik\Kurikulum;
use App\Models\Akademik\ProgramStudi;
use App\Models\Dosen;
use App\Models\Pengaturan\WebSetting;

class MataKuliahController extends Controller
{
    public function renderMataKuliah()
    {
        $user = Auth::user();
        $data['webs'] = WebSetting::first();
        $data['spref'] = $user ? $user->prefix : '';
        $data['menus'] = "Master";
        $data['pages'] = "Mata Kuliah";
        $data['academy'] = $data['webs']->school_apps . ' by ' . $data['webs']->school_name;
        $data['mata_kuliah'] = MataKuliah::with(['kurikulum', 'programStudi', 'dosen1', 'dosen2', 'dosen3'])->get();
        $data['kurikulum'] = Kurikulum::where('status', 'Masih Berlaku')->get();
        $data['program_studi'] = ProgramStudi::where('status', 'Aktif')->get();
        $data['dosens'] = Dosen::all();
        
        return view('master.akademik.mata-kuliah-index', $data, compact('user'));
    }

    public function exportMataKuliahExcel()
    {
        return Excel::download(new MataKuliahExport(), 'data-mata-kuliah-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function exportMataKuliahFullExcel()
    {
        return Excel::download(new MataKuliahFullExport(), 'export-full-mata-kuliah-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function downloadMataKuliahImportTemplate()
    {
        return Excel::download(new MataKuliahImportTemplate(), 'template-import-mata-kuliah.xlsx');
    }

    public function importMataKuliahExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            DB::beginTransaction();
            Excel::import(new MataKuliahImport(), $request->file('file'));
            DB::commit();

            return redirect()->route((Auth::user()->prefix ?? '') . 'akademik.mata-kuliah-render')
                ->with('success', 'Data mata kuliah berhasil diimport.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Import mata kuliah gagal: ' . $e->getMessage());
        }
    }

    public function handleMataKuliah(Request $request)
    {
        try {
            DB::beginTransaction();

            $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:mata_kuliahs,code',
                'kurikulum_id' => 'required|integer',
                'prodi_id' => 'required|integer',
                'requi_id' => 'nullable|integer',
                'dosen1_id' => 'required|integer',
                'dosen2_id' => 'nullable|integer',
                'dosen3_id' => 'nullable|integer',
                'semester' => 'required|integer|min:1|max:14',
                'bsks' => 'required|string|max:10',
                'desc' => 'required|string',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'docs_rps' => 'nullable|file|mimes:pdf|max:2048',
                'docs_kontrak_kuliah' => 'nullable|file|mimes:pdf|max:2048',
            ]);

            // Handle file uploads
            $photo = $request->hasFile('photo') ? $request->file('photo')->store('mata-kuliah/photo', 'public') : 'default.png';
            $docs_rps = $request->hasFile('docs_rps') ? $request->file('docs_rps')->store('mata-kuliah/rps', 'public') : null;
            $docs_kontrak = $request->hasFile('docs_kontrak_kuliah') ? $request->file('docs_kontrak_kuliah')->store('mata-kuliah/kontrak', 'public') : null;
            
            // Create new MataKuliah
            MataKuliah::create([
                'name' => $request->name,
                'code' => trim($request->code),
                'kurikulum_id' => $request->kurikulum_id,
                'prodi_id' => $request->prodi_id,
                'requi_id' => $request->requi_id,
                'dosen1_id' => $request->dosen1_id,
                'dosen2_id' => $request->dosen2_id,
                'dosen3_id' => $request->dosen3_id,
                'semester' => $request->semester,
                'bsks' => $request->bsks,
                'desc' => $request->desc,
                'photo' => $photo,
                'docs_rps' => $docs_rps,
                'docs_kontrak_kuliah' => $docs_kontrak,
                'created_by' => Auth::id(),
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Mata Kuliah berhasil ditambahkan');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menambahkan Mata Kuliah: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function updateMataKuliah(Request $request, $code)
    {
        try {
            DB::beginTransaction();

            $mataKuliah = MataKuliah::where('code', $code)->firstOrFail();

            $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:mata_kuliahs,code,' . $mataKuliah->id,
                'kurikulum_id' => 'required|integer',
                'prodi_id' => 'required|integer',
                'requi_id' => 'nullable|integer',
                'dosen1_id' => 'required|integer',
                'dosen2_id' => 'nullable|integer',
                'dosen3_id' => 'nullable|integer',
                'semester' => 'required|integer|min:1|max:14',
                'bsks' => 'required|string|max:10',
                'desc' => 'required|string',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'docs_rps' => 'nullable|file|mimes:pdf|max:2048',
                'docs_kontrak_kuliah' => 'nullable|file|mimes:pdf|max:2048',
            ]);

            // Handle file uploads
            $photo = $request->hasFile('photo') ? $request->file('photo')->store('mata-kuliah/photo', 'public') : $mataKuliah->photo;
            $docs_rps = $request->hasFile('docs_rps') ? $request->file('docs_rps')->store('mata-kuliah/rps', 'public') : $mataKuliah->docs_rps;
            $docs_kontrak = $request->hasFile('docs_kontrak_kuliah') ? $request->file('docs_kontrak_kuliah')->store('mata-kuliah/kontrak', 'public') : $mataKuliah->docs_kontrak_kuliah;

            $mataKuliah->update([
                'name' => $request->name,
                'code' => trim($request->code),
                'kurikulum_id' => $request->kurikulum_id,
                'prodi_id' => $request->prodi_id,
                'requi_id' => $request->requi_id,
                'dosen1_id' => $request->dosen1_id,
                'dosen2_id' => $request->dosen2_id,
                'dosen3_id' => $request->dosen3_id,
                'semester' => $request->semester,
                'bsks' => $request->bsks,
                'desc' => $request->desc,
                'photo' => $photo,
                'docs_rps' => $docs_rps,
                'docs_kontrak_kuliah' => $docs_kontrak,
                'updated_by' => Auth::id(),
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Mata Kuliah berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal memperbarui Mata Kuliah: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function deleteMataKuliah($code)
    {
        try {
            DB::beginTransaction();

            $mataKuliah = MataKuliah::where('code', $code)->firstOrFail();

            // Optional: Add check if MataKuliah has related JadwalKuliah before deleting
            if ($mataKuliah->jadwalKuliah()->count() > 0) {
                return redirect()->back()->with('error', 'Tidak dapat menghapus Mata Kuliah yang masih memiliki Jadwal Kuliah');
            }

            $mataKuliah->update([
                'deleted_by' => Auth::id()
            ]);
            $mataKuliah->delete();

            DB::commit();
            return redirect()->back()->with('success', 'Mata Kuliah berhasil dihapus');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus Mata Kuliah: ' . $e->getMessage());
        }
    }
}
