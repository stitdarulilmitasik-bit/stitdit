<?php

namespace App\Http\Controllers\Private\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// USE SYSTEM
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
// USE MODELS
use App\Models\Pengaturan\WebSetting;
// USE PLUGINS
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use RealRashid\SweetAlert\Facades\Alert;

class RootController extends Controller
{
    public function renderDashboard()
    {
        $user = Auth::guard('web')->user();
        $data['webs'] = WebSetting::first();
        $data['spref'] = $user ? $user->prefix : '';
        $data['menus'] = "Detail";
        $data['pages'] = "Profile";
        $data['academy'] = $data['webs']->school_apps . ' by ' . $data['webs']->school_name;
        
        return view('central.back-content', $data, compact('user'));
    }

    public function renderProfile()
    {
        $user = Auth::guard('web')->user();
        $data['webs'] = WebSetting::first();
        $data['spref'] = $user ? $user->prefix : '';
        $data['menus'] = "Detail";
        $data['pages'] = "Profile";
        $data['academy'] = $data['webs']->school_apps . ' by ' . $data['webs']->school_name;
        
        return view('central.backpage.profile-index', $data, compact('user'));
    }

    public function handleProfile(Request $request)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                // Personal Information
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

                // Contact Information
                'email' => 'required|email|unique:users,email,' . Auth::guard('web')->id(),
                'phone' => 'required|string|unique:users,phone,' . Auth::guard('web')->id(),
                'link_ig' => 'nullable|url',
                'link_fb' => 'nullable|url',
                'link_in' => 'nullable|url',

                // Address Information
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
                'domicile_poscode' => 'nullable|required_if:domicile_same,No|string|max:10',

                // Education Information
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

                // Identity Information
                'numb_kk' => 'nullable|string|max:20',
                'numb_ktp' => 'nullable|string|max:20',
                'numb_npsn' => 'nullable|string|max:20',
                'numb_nitk' => 'nullable|string|max:20',
                'numb_staff' => 'nullable|string|max:20',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $user = Auth::guard('web')->user();
            $data = $validator->validated();

            // Handle photo upload
            if ($request->hasFile('photo')) {
                /*
                 * Pertahankan NAMA FILE ASLI yang dipilih pengguna.
                 * Contoh: admin1.jpeg tetap menjadi admin1.jpeg.
                 *
                 * File disimpan ke dua lokasi karena ByetHost memakai
                 * /htdocs sebagai document root:
                 *   storage/app/public/images/profile/
                 *   /htdocs/storage/images/profile/
                 * dan juga mirror /htdocs/storage/images/ untuk kompatibilitas
                 * dengan file profile lama yang sudah terlanjur berada di sana.
                 */
                $uploadedFile = $request->file('photo');
                $photoName = basename($uploadedFile->getClientOriginalName());

                // Pastikan nama file tidak kosong dan tidak mengandung path.
                if ($photoName === '' || $photoName === '.' || $photoName === '..') {
                    throw new \RuntimeException('Nama file foto tidak valid.');
                }

                // Ambil nilai kolom photo asli, bukan accessor getPhotoAttribute().
                $oldPhoto = $user->getRawOriginal('photo');

                if ($oldPhoto && $oldPhoto !== 'default.jpg') {
                    $oldName = basename($oldPhoto);
                    Storage::disk('public')->delete('images/profile/' . $oldName);
                    File::delete(storage_path('images/profile/' . $oldName));
                    File::delete(storage_path('images/' . $oldName));
                }

                // Simpan file ASLI tanpa mengganti nama dan tanpa mengubah ekstensi.
                // Dengan demikian admin1.jpeg tetap admin1.jpeg.
                $fileContents = file_get_contents($uploadedFile->getRealPath());
                if ($fileContents === false) {
                    throw new \RuntimeException('File foto tidak dapat dibaca.');
                }

                Storage::disk('public')->put('images/profile/' . $photoName, $fileContents);

                $publicProfileDir = storage_path('images/profile');
                File::ensureDirectoryExists($publicProfileDir);
                File::put($publicProfileDir . '/' . $photoName, $fileContents);

                // Mirror juga ke /htdocs/storage/images agar file lama di lokasi ini
                // tetap dapat digunakan oleh helper avatar.
                $publicImagesDir = storage_path('images');
                File::ensureDirectoryExists($publicImagesDir);
                File::put($publicImagesDir . '/' . $photoName, $fileContents);

                // Pastikan database menyimpan nama file asli yang sama persis.
                $data['photo'] = $photoName;
            }

            $user->update($data);

            return redirect()->back()->with('success', 'Profile updated successfully');
        } catch (\Exception $e) {
            Alert::error('Error', 'Failed to update profile: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }
}
