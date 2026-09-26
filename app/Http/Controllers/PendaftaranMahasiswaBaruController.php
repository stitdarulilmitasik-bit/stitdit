<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Akademik\Fakultas;
use App\Models\Akademik\JenisKelas;
use App\Models\Akademik\ProgramStudi;
use App\Models\Mahasiswa;
use App\Models\Pendaftaran\Pendaftar;
use App\Models\PMB\GelombangPendaftaran;
use App\Models\PMB\JalurPendaftaran;
use App\Models\Pengaturan\WebSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PendaftaranMahasiswaBaruController extends Controller
{
    public function index()
    {
        $data['webs'] = WebSetting::first();
        $data['pages'] = 'Pendaftaran Calon Mahasiswa Baru';
        $data['menus'] = 'Kemahasiswaan';
        $data['academy'] = ($data['webs']->school_apps ?? 'SIAKAD') . ' by ' . ($data['webs']->school_name ?? 'STIT Darul Ilmi');

        $data['fakultas'] = Fakultas::where('status', 'Aktif')
            ->with(['programStudis' => function ($query) {
                $query->where('status', 'Aktif')->orderBy('name');
            }])
            ->orderBy('name')
            ->get();

        $data['jenisKelas'] = JenisKelas::orderBy('name')->get();

        $data['jalurs'] = JalurPendaftaran::with('periode')
            ->whereHas('periode', function ($query) {
                $query->whereDate('start_date', '<=', now()->toDateString())
                    ->whereDate('ended_date', '>=', now()->toDateString());
            })
            ->orderBy('name')
            ->get();

        $data['gelombangs'] = GelombangPendaftaran::with('jalur.periode')
            ->whereDate('start_date', '<=', now()->toDateString())
            ->whereDate('ended_date', '>=', now()->toDateString())
            ->orderBy('name')
            ->get();

        return view('central.pages.pendaftaran-mahasiswa-baru', $data);
    }

    public function store(Request $request)
    {
        $request->merge([
            'phone' => preg_replace('/\D+/', '', (string) $request->input('phone')),
            'numb_ktp' => preg_replace('/\D+/', '', (string) $request->input('numb_ktp')),
        ]);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:mahasiswas,email',
            'phone' => 'required|string|max:30|unique:mahasiswas,phone',
            'numb_ktp' => 'required|string|max:30|unique:mahasiswas,numb_ktp',
            'bio_gender' => 'required|in:Laki-laki,Perempuan',
            'bio_placebirth' => 'required|string|max:255',
            'bio_datebirth' => 'required|date',
            'bio_religion' => 'required|string|max:50',
            'ktp_addres' => 'required|string|max:1000',
            'ktp_rt' => 'required|string|max:10',
            'ktp_rw' => 'required|string|max:10',
            'ktp_village' => 'required|string|max:255',
            'ktp_subdistrict' => 'required|string|max:255',
            'ktp_city' => 'required|string|max:255',
            'ktp_province' => 'required|string|max:255',
            'ktp_poscode' => 'required|string|max:10',
            'fakultas_id' => 'required|exists:fakultas,id',
            'prodi_id' => 'required|exists:program_studis,id',
            'jenis_id' => 'required|exists:jenis_kelas,id',
            'jalur_id' => 'required|exists:jalur_pendaftarans,id',
            'gelombang_id' => 'required|exists:gelombang_pendaftarans,id',
        ]);

        $prodi = ProgramStudi::where('id', $request->prodi_id)
            ->where('fakultas_id', $request->fakultas_id)
            ->where('status', 'Aktif')
            ->first();

        if (!$prodi) {
            return back()->withErrors(['prodi_id' => 'Program studi tidak sesuai dengan fakultas tujuan.'])
                ->withInput();
        }

        $gelombang = GelombangPendaftaran::with('jalur.periode')
            ->whereKey($request->gelombang_id)
            ->whereDate('start_date', '<=', now()->toDateString())
            ->whereDate('ended_date', '>=', now()->toDateString())
            ->first();

        if (!$gelombang || (int) $gelombang->jalur_id !== (int) $request->jalur_id) {
            return back()->withErrors(['gelombang_id' => 'Gelombang pendaftaran yang dipilih tidak aktif atau tidak sesuai dengan jalur.'])
                ->withInput();
        }

        DB::beginTransaction();

        try {
            $mahasiswaCode = 'MHS-' . strtoupper(Str::random(8));
            $pendaftarCode = 'PMB-' . strtoupper(Str::random(8));
            $numbReg = 'REG-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));

            $mahasiswa = Mahasiswa::create([
                'type' => 0,
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'bio_gender' => $request->bio_gender,
                'bio_placebirth' => $request->bio_placebirth,
                'bio_datebirth' => $request->bio_datebirth,
                'bio_religion' => $request->bio_religion,
                'ktp_addres' => $request->ktp_addres,
                'ktp_rt' => $request->ktp_rt,
                'ktp_rw' => $request->ktp_rw,
                'ktp_village' => $request->ktp_village,
                'ktp_subdistrict' => $request->ktp_subdistrict,
                'ktp_city' => $request->ktp_city,
                'ktp_province' => $request->ktp_province,
                'ktp_poscode' => $request->ktp_poscode,
                'numb_ktp' => $request->numb_ktp,
                'numb_reg' => $numbReg,
                'code' => $mahasiswaCode,
                'password' => Hash::make($mahasiswaCode),
                'prodi_id' => $prodi->id,
                'taka_regist' => optional($gelombang->jalur?->periode)->taka_id,
            ]);

            Pendaftar::create([
                'mahasiswa_id' => $mahasiswa->id,
                'jalur_id' => $request->jalur_id,
                'jenis_id' => $request->jenis_id,
                'prodi_1' => $prodi->id,
                'prodi_2' => $prodi->id,
                'gelombang_id' => $request->gelombang_id,
                'phone' => $request->phone,
                'email' => $request->email,
                'name' => $request->name,
                'code' => $pendaftarCode,
                'numb_reg' => $numbReg,
                'register_date' => now(),
                'status' => 'Pending',
            ]);

            DB::commit();

            return redirect()->route('root.pendaftaran-mahasiswa-baru')
                ->with('registration_success', [
                    'name' => $request->name,
                    'numb_reg' => $numbReg,
                    'code' => $pendaftarCode,
                    'prodi' => $prodi->name,
                ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->with('error', 'Pendaftaran belum dapat disimpan. Silakan periksa kembali data yang diisi.')
                ->withInput();
        }
    }
}
