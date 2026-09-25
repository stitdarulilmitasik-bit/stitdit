<?php

namespace App\Http\Controllers\Private\Dosen;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Pengaturan\WebSetting;
use App\Models\Akademik\JadwalKuliah;
use App\Models\Akademik\MataKuliah;
use App\Models\Akademik\KRS;
use Illuminate\Support\Facades\File;
use Barryvdh\DomPDF\Facade\Pdf;

class RootController extends Controller
{
    public function renderProfile()
    {
        $user = Auth::guard('dosen')->user();
        $data['webs'] = WebSetting::first();
        $data['spref'] = $user ? $user->prefix : '';
        $data['menus'] = "Detail";
        $data['pages'] = "Profile Dosen";
        $data['academy'] = $data['webs']->school_apps . ' by ' . $data['webs']->school_name;

        return view('central.backpage.profile-dosen', $data, compact('user'));
    }

    public function handleProfile(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'title_front' => 'nullable|string|max:50',
                'title_behind' => 'nullable|string|max:50',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:8192',
                'bio_placebirth' => 'nullable|string|max:100',
                'bio_datebirth' => 'nullable|date',
                'bio_gender' => 'nullable|in:Laki-laki,Perempuan',
                'bio_religion' => 'nullable|string|max:50',
                'bio_nationality' => 'nullable|string|max:50',
                'bio_blood' => 'nullable|string|max:5',
                'bio_height' => 'nullable|numeric',
                'bio_weight' => 'nullable|numeric',
                'email' => 'required|email|unique:dosens,email,' . Auth::guard('dosen')->user()->id,
                'phone' => 'required|string|unique:dosens,phone,' . Auth::guard('dosen')->user()->id,
                'link_ig' => 'nullable|url',
                'link_fb' => 'nullable|url',
                'link_in' => 'nullable|url',
                'ktp_addres' => 'nullable|string',
                'ktp_rt' => 'nullable|string|max:10',
                'ktp_rw' => 'nullable|string|max:10',
                'ktp_village' => 'nullable|string|max:100',
                'ktp_subdistrict' => 'nullable|string|max:100',
                'ktp_city' => 'nullable|string|max:100',
                'ktp_province' => 'nullable|string|max:100',
                'ktp_poscode' => 'nullable|string|max:10',
                'domicile_same' => 'required|in:Yes,No',
                'domicile_addres' => 'nullable|required_if:domicile_same,No|string',
                'domicile_rt' => 'nullable|required_if:domicile_same,No|string|max:10',
                'domicile_rw' => 'nullable|required_if:domicile_same,No|string|max:10',
                'domicile_village' => 'nullable|required_if:domicile_same,No|string|max:100',
                'domicile_subdistrict' => 'nullable|required_if:domicile_same,No|string|max:100',
                'domicile_city' => 'nullable|required_if:domicile_same,No|string|max:100',
                'domicile_province' => 'nullable|required_if:domicile_same,No|string|max:100',
                'domicile_poscode' => 'nullable|string|max:10',
                'edu1_type' => 'required|in:SMA/SMK,Diploma,Sarjana,Magister,Doktor',
                'edu1_place' => 'required|string|max:255',
                'edu1_major' => 'required|string|max:255',
                'edu1_average_score' => 'required|string|max:10',
                'edu1_graduate_year' => 'required|string|max:4',
                'edu2_type' => 'nullable|in:SMA/SMK,Diploma,Sarjana,Magister,Doktor',
                'edu2_place' => 'nullable|string|max:255',
                'edu2_major' => 'nullable|string|max:255',
                'edu2_average_score' => 'nullable|string|max:10',
                'edu2_graduate_year' => 'nullable|string|max:4',
                'edu3_type' => 'nullable|in:SMA/SMK,Diploma,Sarjana,Magister,Doktor',
                'edu3_place' => 'nullable|string|max:255',
                'edu3_major' => 'nullable|string|max:255',
                'edu3_average_score' => 'nullable|string|max:10',
                'edu3_graduate_year' => 'nullable|string|max:4',
                'numb_kk' => 'nullable|string|max:20',
                'numb_ktp' => 'nullable|string|max:20',
                'numb_npsn' => 'nullable|string|max:20',
                'numb_nidn' => 'nullable|string|max:20',
                'numb_nitk' => 'nullable|string|max:20',
                'numb_staff' => 'nullable|string|max:20',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $user = Auth::guard('dosen')->user();
            $data = $validator->validated();

            if ($request->hasFile('photo')) {
                /*
                 * Samakan dengan mekanisme upload Web Admin:
                 * - gunakan nama file asli
                 * - jangan resize/encode ulang dengan GD/Intervention
                 * - simpan ke public disk dan mirror fisik ByetHost
                 * - database menyimpan nama file asli yang sama persis
                 */
                $uploadedFile = $request->file('photo');
                $photoName = basename($uploadedFile->getClientOriginalName());

                if ($photoName === '' || $photoName === '.' || $photoName === '..') {
                    throw new \RuntimeException('Nama file foto tidak valid.');
                }

                $oldPhoto = $user->getRawOriginal('photo');

                if ($oldPhoto && $oldPhoto !== 'default.jpg') {
                    $oldName = basename($oldPhoto);
                    try {
                        Storage::disk('public')->delete('images/profile/' . $oldName);
                    } catch (\Throwable $e) {
                        // Foto lama tidak boleh menggagalkan upload baru.
                    }
                    File::delete(storage_path('images/profile/' . $oldName));
                    File::delete(storage_path('images/' . $oldName));
                }

                $fileContents = file_get_contents($uploadedFile->getRealPath());
                if ($fileContents === false) {
                    throw new \RuntimeException('File foto tidak dapat dibaca.');
                }

                Storage::disk('public')->put('images/profile/' . $photoName, $fileContents);

                $publicProfileDir = storage_path('images/profile');
                File::ensureDirectoryExists($publicProfileDir);
                File::put($publicProfileDir . '/' . $photoName, $fileContents);

                $publicImagesDir = storage_path('images');
                File::ensureDirectoryExists($publicImagesDir);
                File::put($publicImagesDir . '/' . $photoName, $fileContents);

                $data['photo'] = $photoName;
            }
            $user->update($data);
            return redirect()->back()->with('success', 'Profile updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update profile: ' . $e->getMessage())->withInput();
        }
    }

    public function renderDashboard()
    {
        $user = Auth::guard('dosen')->user();
        abort_unless($user, 403);

        $webs = WebSetting::first();
        $mataKuliah = MataKuliah::where(function ($q) use ($user) {
            $q->where('dosen1_id', $user->id)
              ->orWhere('dosen2_id', $user->id)
              ->orWhere('dosen3_id', $user->id);
        })->with('programStudi')->get();

        $mataKuliahIds = $mataKuliah->pluck('id');
        $jadwal = JadwalKuliah::with(['mataKuliah.programStudi', 'ruang', 'jenisKelas', 'waktuKuliah', 'kelas'])
            ->where(function ($q) use ($user, $mataKuliahIds) {
                $q->where('dosen_id', $user->id);
                if ($mataKuliahIds->isNotEmpty()) {
                    $q->orWhereIn('matkul_id', $mataKuliahIds);
                }
            })
            ->orderByRaw('CASE WHEN tanggal IS NULL THEN 1 ELSE 0 END')
            ->orderBy('tanggal')
            ->orderBy('waktu_kuliah_id')
            ->get();

        $distribution = $mataKuliah->groupBy(function ($item) {
            return $item->programStudi->name ?? $item->programStudi->nama ?? 'Belum Ditentukan';
        })->map->count()->sortDesc();

        $krsPending = 0;
        try {
            $krsPending = KRS::where('status', 'Diajukan')
                ->whereHas('details', function ($q) use ($user) {
                    $q->where('dosen_id', $user->id);
                })->count();
        } catch (\Throwable $e) {
            $krsPending = 0;
        }

        $activities = collect();
        foreach ($mataKuliah->sortByDesc('created_at')->take(5) as $item) {
            $activities->push([
                'title' => 'Mata Kuliah',
                'description' => ($item->name ?? 'Mata kuliah') . ' berada dalam daftar ampuan Anda',
                'time' => $item->created_at,
                'icon' => 'fa-book',
            ]);
        }
        foreach ($jadwal->sortByDesc('created_at')->take(5) as $item) {
            $activities->push([
                'title' => 'Jadwal Kuliah',
                'description' => 'Jadwal ' . ($item->mataKuliah->name ?? 'perkuliahan') . ' ditambahkan',
                'time' => $item->created_at,
                'icon' => 'fa-calendar-alt',
            ]);
        }
        $activities = $activities->sortByDesc('time')->take(10);

        return view('private.dosen.dashboard', [
            'user' => $user,
            'webs' => $webs,
            'spref' => 'dosen.',
            'menus' => 'Dashboard',
            'pages' => 'Dashboard Dosen',
            'academy' => $webs ? $webs->school_apps . ' by ' . $webs->school_name : 'SIAKAD',
            'totalStudents' => $jadwal->flatMap(fn ($j) => $j->kelas)->flatMap(fn ($k) => $k->mahasiswas ?? collect())->unique('id')->count(),
            'activeCourses' => $mataKuliah->count(),
            'totalFaculty' => 1,
            'totalEvents' => $jadwal->count(),
            'krsPending' => $krsPending,
            'distribution' => $distribution,
            'activities' => $activities,
            'jadwalSaya' => $jadwal,
        ]);
    }

    public function exportJadwalPdf()
    {
        $user = Auth::guard('dosen')->user();
        abort_unless($user, 403);

        $mataKuliahIds = MataKuliah::where(function ($q) use ($user) {
            $q->where('dosen1_id', $user->id)
              ->orWhere('dosen2_id', $user->id)
              ->orWhere('dosen3_id', $user->id);
        })->pluck('id');

        $jadwalSaya = JadwalKuliah::with(['mataKuliah', 'ruang', 'waktuKuliah', 'kelas'])
            ->where(function ($q) use ($user, $mataKuliahIds) {
                $q->where('dosen_id', $user->id);
                if ($mataKuliahIds->isNotEmpty()) {
                    $q->orWhereIn('matkul_id', $mataKuliahIds);
                }
            })
            ->orderByRaw('CASE WHEN tanggal IS NULL THEN 1 ELSE 0 END')
            ->orderBy('tanggal')
            ->orderBy('waktu_kuliah_id')
            ->get();

        $logo = null;
        $logoCandidates = [
            storage_path('app/public/images/logo/logo-vert.png'),
            public_path('storage/images/logo/logo-vert.png'),
        ];
        foreach ($logoCandidates as $candidate) {
            if (is_file($candidate)) {
                $mime = mime_content_type($candidate) ?: 'image/png';
                $logo = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($candidate));
                break;
            }
        }

        $pdf = Pdf::loadView('private.dosen.jadwal-pdf', compact('user', 'jadwalSaya', 'logo'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('Jadwal-Mengajar-Dosen-' . str()->slug($user->name) . '.pdf');
    }
}
