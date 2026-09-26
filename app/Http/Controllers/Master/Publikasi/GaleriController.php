<?php

namespace App\Http\Controllers\Master\Publikasi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Publikasi\Galeri;
use App\Models\Publikasi\GaleriFoto;
use App\Models\Publikasi\Kategori;
use App\Models\Pengaturan\WebSetting;

class GaleriController extends Controller
{
    public function renderGaleri()
    {
        $user = Auth::user();
        $data['webs'] = WebSetting::first();
        $data['spref'] = $user ? $user->prefix : '';
        $data['menus'] = 'Master';
        $data['pages'] = 'Galeri';
        $data['academy'] = $data['webs'] ? $data['webs']->school_apps . ' by ' . $data['webs']->school_name : 'STIT Darul Ilmi Tasikmalaya';
        $data['galeri'] = Galeri::latest()->get();
        $data['kategori'] = Kategori::all();
        return view('master.publikasi.galeri-index', $data, compact('user'));
    }

    public function viewGaleri($code)
    {
        $user = Auth::user();
        $data['webs'] = WebSetting::first();
        $data['spref'] = $user ? $user->prefix : '';
        $data['menus'] = 'Master';
        $data['pages'] = 'Galeri';
        $data['academy'] = $data['webs'] ? $data['webs']->school_apps . ' by ' . $data['webs']->school_name : 'STIT Darul Ilmi Tasikmalaya';
        $data['galeri'] = Galeri::where('code', $code)->with(['kategori', 'fotos'])->firstOrFail();
        return view('master.publikasi.galeri-view', $data, compact('user'));
    }

    public function handleGaleri(Request $request)
    {
        try {
            DB::beginTransaction();

            $request->validate([
                'kategori_id' => 'required|exists:kategoris,id',
                'name' => 'required|string|max:255',
                'content' => 'required|string',
                'photo' => 'required|image|mimes:jpeg,png,jpg,webp|max:4096',
                'photos' => 'nullable|array',
                'photos.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
                'desc' => 'nullable|string|max:1000',
                'status' => 'required|in:Draft,Publish,Archive'
            ]);

            $slug = Str::slug($request->name);
            $code = 'GLR-' . strtoupper(Str::random(8));
            $photoName = $request->file('photo')->getClientOriginalName();
            $request->photo->storeAs('images/galeri/' . $code, $photoName, 'public');

            $galeri = Galeri::create([
                'code' => $code,
                'kategori_id' => $request->kategori_id,
                'name' => $request->name,
                'slug' => $slug,
                'content' => $request->content,
                'photo' => $photoName,
                'status' => $request->status,
                'created_by' => Auth::id()
            ]);

            foreach ($request->file('photos', []) as $photo) {
                $fotoCode = 'FTO-' . strtoupper(Str::random(8));
                $fotoName = $photo->getClientOriginalName();
                $photo->storeAs('images/galeri/foto/' . $code, $fotoName, 'public');

                GaleriFoto::create([
                    'code' => $fotoCode,
                    'galeri_id' => $galeri->id,
                    'photo' => $fotoName,
                    'desc' => $request->desc,
                    'created_by' => Auth::id()
                ]);
            }

            DB::commit();
            $spref = Auth::user() ? Auth::user()->prefix : '';
            return redirect()->route($spref . 'publikasi.galeri-view', $code)->with('success', 'Galeri dan dokumentasi foto berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function updateGaleri(Request $request, $code)
    {
        try {
            DB::beginTransaction();
            $galeri = Galeri::where('code', $code)->firstOrFail();
            $request->validate([
                'kategori_id' => 'required|exists:kategoris,id',
                'name' => 'required|string|max:255',
                'content' => 'required|string',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
                'status' => 'required|in:Draft,Publish,Archive'
            ]);
            $updateData = [
                'kategori_id' => $request->kategori_id,
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'content' => $request->content,
                'status' => $request->status,
                'updated_by' => Auth::id()
            ];
            if ($request->hasFile('photo')) {
                if ($galeri->photo) { Storage::disk('public')->delete('images/galeri/' . $code . '/' . $galeri->photo); Storage::disk('public')->delete('images/galeri/' . $galeri->photo); }
                $photoName = $request->file('photo')->getClientOriginalName();
                $request->photo->storeAs('images/galeri/' . $code, $photoName, 'public');
                $updateData['photo'] = $photoName;
            }
            $galeri->update($updateData);
            DB::commit();
            $spref = Auth::user() ? Auth::user()->prefix : '';
            return redirect()->route($spref . 'publikasi.galeri-render')->with('success', 'Data galeri berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function deleteGaleri($code)
    {
        try {
            DB::beginTransaction();
            $galeri = Galeri::where('code', $code)->firstOrFail();
            if ($galeri->photo) { Storage::disk('public')->delete('images/galeri/' . $galeri->code . '/' . $galeri->photo); Storage::disk('public')->delete('images/galeri/' . $galeri->photo); }
            foreach ($galeri->fotos as $foto) {
                Storage::disk('public')->delete('images/galeri/foto/' . $galeri->code . '/' . $foto->photo); Storage::disk('public')->delete('images/galeri/foto/' . $foto->photo);
                $foto->update(['deleted_by' => Auth::id()]);
                $foto->delete();
            }
            $galeri->update(['deleted_by' => Auth::id()]);
            $galeri->delete();
            DB::commit();
            return redirect()->back()->with('success', 'Galeri berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus galeri: ' . $e->getMessage());
        }
    }

    public function handleFoto(Request $request, $code)
    {
        try {
            DB::beginTransaction();
            $galeri = Galeri::where('code', $code)->firstOrFail();
            $request->validate([
                'photos' => 'required|array|min:1',
                'photos.*' => 'required|image|mimes:jpeg,png,jpg,webp|max:4096',
                'desc' => 'nullable|string|max:1000'
            ]);
            foreach ($request->file('photos', []) as $photo) {
                $fotoCode = 'FTO-' . strtoupper(Str::random(8));
                $photoName = $photo->getClientOriginalName();
                $photo->storeAs('images/galeri/foto/' . $code, $photoName, 'public');
                GaleriFoto::create([
                    'code' => $fotoCode,
                    'galeri_id' => $galeri->id,
                    'photo' => $photoName,
                    'desc' => $request->desc,
                    'created_by' => Auth::id()
                ]);
            }
            DB::commit();
            $spref = Auth::user() ? Auth::user()->prefix : '';
            return redirect()->route($spref . 'publikasi.galeri-view', $code)->with('success', 'Semua foto berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function serveCover($code)
    {
        $galeri = Galeri::where('code', $code)->firstOrFail();
        $paths = [
            'images/galeri/' . $galeri->code . '/' . $galeri->photo,
            'images/galeri/' . $galeri->photo,
        ];
        foreach ($paths as $path) {
            if ($galeri->photo && Storage::disk('public')->exists($path)) {
                return response()->file(Storage::disk('public')->path($path));
            }
        }
        abort(404);
    }

    public function serveFoto($code)
    {
        $foto = GaleriFoto::where('code', $code)->firstOrFail();
        $galeri = Galeri::find($foto->galeri_id);
        $paths = [
            $galeri ? 'images/galeri/foto/' . $galeri->code . '/' . $foto->photo : null,
            'images/galeri/foto/' . $foto->photo,
        ];
        foreach ($paths as $path) {
            if ($path && Storage::disk('public')->exists($path)) {
                return response()->file(Storage::disk('public')->path($path));
            }
        }
        abort(404);
    }

    public function deleteFoto($code)
    {
        try {
            DB::beginTransaction();
            $foto = GaleriFoto::where('code', $code)->firstOrFail();
            $galeri = Galeri::find($foto->galeri_id);
            if ($foto->photo) { if ($galeri) Storage::disk('public')->delete('images/galeri/foto/' . $galeri->code . '/' . $foto->photo); Storage::disk('public')->delete('images/galeri/foto/' . $foto->photo); }
            $foto->update(['deleted_by' => Auth::id()]);
            $foto->delete();
            DB::commit();
            return redirect()->back()->with('success', 'Foto berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus foto: ' . $e->getMessage());
        }
    }
}
