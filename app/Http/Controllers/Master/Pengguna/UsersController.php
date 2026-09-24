<?php

namespace App\Http\Controllers\Master\Pengguna;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// Use System
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
// Use Models
use App\Models\User;
use App\Models\Pengaturan\WebSetting;
// Use Plugins

class UsersController extends Controller
{
    /**
     * Hanya Web Administrator (type 0) yang boleh mengelola akun User.
     * Staff/Operator hanya memiliki akses baca pada halaman Pengguna.
     */
    private function ensureAdministrator(): void
    {
        $currentUser = Auth::guard('web')->user();
        $rawType = $currentUser?->getRawOriginal('type');

        abort_unless(
            $currentUser && (int) $rawType === 0,
            403,
            'Akses pengelolaan pengguna hanya untuk Administrator.'
        );
    }

    public function renderUsers()
    {
        $user = Auth::guard('web')->user();
        $data['webs'] = WebSetting::first();
        $data['spref'] = $user ? $user->prefix : '';
        $data['menus'] = "Master";
        $data['pages'] = "Pengguna";
        $data['academy'] = $data['webs']->school_apps . ' by ' . $data['webs']->school_name;
        $data['users'] = User::latest()->get();
        
        return view('master.pengguna.users-index', $data, compact('user'));
    }


    public function exportUsersPDF()
    {
        try {
            $users = User::latest()->get();

            $data = [
                'users' => $users,
                'webs' => WebSetting::first(),
            ];

            $pdf = Pdf::loadView('master.pengguna.users-pdf', $data)
                ->setPaper('a4', 'landscape');

            return $pdf->download('daftar-pengguna-' . now()->format('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal export PDF pengguna: ' . $e->getMessage());
        }
    }

    public function viewUsers($code)
    {
        $user = Auth::guard('web')->user();
        $data['webs'] = WebSetting::first();
        $data['spref'] = $user ? $user->prefix : '';
        $data['menus'] = "Master";
        $data['pages'] = "Pengguna";
        $data['academy'] = $data['webs']->school_apps . ' by ' . $data['webs']->school_name;
        $data['users'] = User::where('code', $code)->first();
        
        return view('master.pengguna.users-views', $data, compact('user'));
    }

    public function handleProfile(Request $request, $code)
    {
        // Profil pengguna pada menu Master hanya dapat diubah Administrator.
        // Staff/Operator memiliki akses baca saja.
        $this->ensureAdministrator();

        try {
            DB::beginTransaction();
            
            $user = User::where('code', $code)->firstOrFail();
            
            // Base validation rules
            $rules = [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $user->id,
                'phone' => 'required|string|unique:users,phone,' . $user->id,
                'title_front' => 'nullable|string|max:50',
                'title_behind' => 'nullable|string|max:50',
                'bio_placebirth' => 'nullable|string|max:100',
                'bio_datebirth' => 'nullable|date',
                'bio_gender' => 'nullable|string|in:Laki-laki,Perempuan',
                'bio_religion' => 'nullable|string|max:50',
                'bio_nationality' => 'nullable|string|max:50',
                'bio_blood' => 'nullable|string|max:5',
                'bio_height' => 'nullable|numeric',
                'bio_weight' => 'nullable|numeric',
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
                'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
            ];

            // Add conditional validation for domicile address
            if ($request->domicile_same === 'No') {
                $rules['domicile_addres'] = 'required|string';
                $rules['domicile_rt'] = 'required|string|max:10';
                $rules['domicile_rw'] = 'required|string|max:10';
                $rules['domicile_village'] = 'required|string|max:100';
                $rules['domicile_subdistrict'] = 'required|string|max:100';
                $rules['domicile_city'] = 'required|string|max:100';
                $rules['domicile_province'] = 'required|string|max:100';
                $rules['domicile_poscode'] = 'required|string|max:10';
            } else {
                $rules['domicile_addres'] = 'nullable|string';
                $rules['domicile_rt'] = 'nullable|string|max:10';
                $rules['domicile_rw'] = 'nullable|string|max:10';
                $rules['domicile_village'] = 'nullable|string|max:100';
                $rules['domicile_subdistrict'] = 'nullable|string|max:100';
                $rules['domicile_city'] = 'nullable|string|max:100';
                $rules['domicile_province'] = 'nullable|string|max:100';
                $rules['domicile_poscode'] = 'nullable|string|max:10';
            }

            // Add validation for education fields (all optional)
            $rules = array_merge($rules, [
                'edu1_type' => 'nullable|string|in:SMA/SMK,Diploma,Sarjana,Magister,Doktor',
                'edu1_place' => 'nullable|string|max:255',
                'edu1_major' => 'nullable|string|max:255',
                'edu1_average_score' => 'nullable|string',
                'edu1_graduate_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
                'edu2_type' => 'nullable|string|in:SMA/SMK,Diploma,Sarjana,Magister,Doktor',
                'edu2_place' => 'nullable|string|max:255',
                'edu2_major' => 'nullable|string|max:255',
                'edu2_average_score' => 'nullable|string',
                'edu2_graduate_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
                'edu3_type' => 'nullable|string|in:SMA/SMK,Diploma,Sarjana,Magister,Doktor',
                'edu3_place' => 'nullable|string|max:255',
                'edu3_major' => 'nullable|string|max:255',
                'edu3_average_score' => 'nullable|string',
                'edu3_graduate_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
                'numb_kk' => 'nullable|string|max:20',
                'numb_ktp' => 'nullable|string|max:20',
                'numb_npsn' => 'nullable|string|max:20',
                'numb_nitk' => 'nullable|string|max:20',
                'numb_staff' => 'nullable|string|max:20'
            ]);

            // dd($rules);

            $request->validate($rules);

            $updateData = $request->except(['_token', '_method', 'photo']);
            
            // Handle photo upload
            if ($request->hasFile('photo')) {
                // Hapus foto lama
                if ($user->getRawOriginal('photo') && $user->getRawOriginal('photo') !== 'default.jpg') {
                    Storage::disk('public')->delete('images/profile/' . $user->getRawOriginal('photo'));
                }
            
                // Simpan foto baru
                $photoName = time() . '-' . $user->code . '-' . uniqid() .'.' . $request->photo->getClientOriginalExtension();
                $request->photo->storeAs('images/profile', $photoName, 'public');
                $updateData['photo'] = $photoName;
            }

            // Handle domicile address
            if ($request->domicile_same === 'Yes') {
                $updateData['domicile_addres'] = $request->ktp_addres;
                $updateData['domicile_rt'] = $request->ktp_rt;
                $updateData['domicile_rw'] = $request->ktp_rw;
                $updateData['domicile_village'] = $request->ktp_village;
                $updateData['domicile_subdistrict'] = $request->ktp_subdistrict;
                $updateData['domicile_city'] = $request->ktp_city;
                $updateData['domicile_province'] = $request->ktp_province;
                $updateData['domicile_poscode'] = $request->ktp_poscode;
            }

            $updateData['updated_by'] = Auth::guard('web')->id();
            $user->update($updateData);

            DB::commit();
            $spref = Auth::guard('web')->user() ? Auth::guard('web')->user()->prefix : '';
            return redirect()->route($spref . 'pengguna.users-views', $code)->with('success', 'Profile berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function handleUsers(Request $request)
    {
        $this->ensureAdministrator();

        try {
            DB::beginTransaction();
            
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'phone' => 'required|string|unique:users,phone',
                'password' => 'required|string|min:6',
                'type' => 'required|integer|between:0,7'
            ]);

            $code = 'USR-' . strtoupper(Str::random(8));
            
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'code' => $code,
                'type' => $request->type,
                'created_by' => Auth::guard('web')->id()
            ]);

            DB::commit();
            $spref = Auth::guard('web')->user() ? Auth::guard('web')->user()->prefix : '';
            return redirect()->route($spref . 'pengguna.users-render')->with('success', 'Pengguna berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function updateUsers(Request $request, $code)
    {
        $this->ensureAdministrator();

        try {
            DB::beginTransaction();
            
            $user = User::where('code', $code)->firstOrFail();
            
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $user->code . ',code',
                'phone' => 'required|string|unique:users,phone,' . $user->code . ',code',
                'password' => 'nullable|string|min:6',
                'type' => 'required|integer|between:0,7'
            ]);

            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'type' => $request->type,
                'updated_by' => Auth::guard('web')->id()
            ];
            
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }
            
            $user->update($updateData);

            DB::commit();
            $spref = Auth::guard('web')->user() ? Auth::guard('web')->user()->prefix : '';
            return redirect()->route($spref . 'pengguna.users-render')->with('success', 'Data pengguna berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function deleteUsers($code)
    {
        $this->ensureAdministrator();

        $currentUserId = Auth::guard('web')->id();

        try {
            if (!$currentUserId) {
                return redirect()->route('auth.render-signin');
            }

            $user = User::where('code', $code)->firstOrFail();

            // Hanya Administrator yang sampai ke method ini. Akun sendiri
            // tetap dilindungi agar tidak terhapus secara tidak sengaja.
            if ((int) $user->id === (int) $currentUserId) {
                return redirect()->back()->with('error', 'Tidak dapat menghapus akun yang sedang digunakan');
            }

            DB::transaction(function () use ($user, $currentUserId) {
                // Simpan pelaku penghapusan sebelum SoftDeletes.
                $user->deleted_by = $currentUserId;
                $user->saveQuietly();

                // Model User menggunakan SoftDeletes, sehingga data tetap
                // tersimpan dan dapat dipulihkan dari basis data bila diperlukan.
                $user->delete();
            });

            return redirect()->back()->with('success', 'Pengguna berhasil dihapus');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'Pengguna tidak ditemukan atau sudah dihapus.');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->with('error', 'Gagal menghapus pengguna. Silakan periksa log aplikasi untuk detail kesalahan.');
        }
    }
}
