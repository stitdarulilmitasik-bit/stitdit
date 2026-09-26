<?php

namespace App\Http\Controllers\Master\Layanan;

use App\Http\Controllers\Controller;
use App\Models\Layanan\LegalisirPengajuan;
use App\Models\Pengaturan\WebSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LegalisirController extends Controller
{
    private function authorizeManager(): void
    {
        $user = Auth::user();

        if (!$user || !in_array((int) $user->raw_type, [0, 1], true)) {
            abort(403, 'Anda tidak memiliki akses untuk mengelola legalisir.');
        }
    }

    public function index(Request $request)
    {
        $this->authorizeManager();

        $query = LegalisirPengajuan::with('mahasiswa')
            ->latest('tanggal_pengajuan')
            ->latest('id');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->whereHas('mahasiswa', function ($student) use ($q) {
                $student->where('name', 'like', '%' . $q . '%')
                    ->orWhere('numb_nim', 'like', '%' . $q . '%');
            });
        }

        return view('master.layanan.legalisir-index', [
            'webs' => WebSetting::first(),
            'user' => Auth::user(),
            'menus' => 'Layanan',
            'pages' => 'Legalisir Dokumen',
            'academy' => 'STIT Darul Ilmi Tasikmalaya',
            'pengajuan' => $query->paginate(20)->withQueryString(),
        ]);
    }

    public function show($id)
    {
        $this->authorizeManager();

        return view('master.layanan.legalisir-detail', [
            'webs' => WebSetting::first(),
            'user' => Auth::user(),
            'menus' => 'Layanan',
            'pages' => 'Detail Legalisir',
            'academy' => 'STIT Darul Ilmi Tasikmalaya',
            'item' => LegalisirPengajuan::with('mahasiswa')->findOrFail($id),
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->authorizeManager();

        $item = LegalisirPengajuan::findOrFail($id);

        $data = $request->validate([
            'status' => 'required|in:Diajukan,Diverifikasi,Disetujui,Ditolak,Diproses,Selesai',
            'catatan_admin' => 'nullable|string|max:3000',
            'nomor_legalisir' => 'nullable|string|max:100',
            'tanggal_legalisir' => 'nullable|date',
            'pejabat_nama' => 'nullable|string|max:150',
            'pejabat_jabatan' => 'nullable|string|max:150',
            'file_hasil' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if ($request->hasFile('file_hasil')) {
            if ($item->file_hasil && Storage::disk('public')->exists($item->file_hasil)) {
                Storage::disk('public')->delete($item->file_hasil);
            }

            $data['file_hasil'] = $request->file('file_hasil')->store('legalisir/hasil', 'public');
        }

        if ($data['status'] === 'Selesai') {
            if (empty($data['nomor_legalisir'] ?? $item->nomor_legalisir)) {
                return back()->withErrors(['nomor_legalisir' => 'Nomor legalisir wajib diisi sebelum status Selesai.'])->withInput();
            }

            $data['tanggal_legalisir'] = $data['tanggal_legalisir'] ?? $item->tanggal_legalisir ?? now()->toDateString();
        }

        $data['diproses_oleh'] = Auth::id();
        $item->update($data);

        return redirect()
            ->route('web-admin.layanan.legalisir.detail', $item->id)
            ->with('success', 'Pengajuan legalisir berhasil diperbarui.');
    }
}
