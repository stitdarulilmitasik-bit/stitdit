<?php

namespace App\Http\Controllers\Private\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Pengaturan\WebSetting;
use App\Models\Akademik\KRS;
use App\Models\Akademik\JadwalKuliah;
use App\Models\Akademik\Nilai;
use App\Models\Akademik\MataKuliah;
use App\Models\Akademik\TahunAkademik;
use App\Models\Akademik\Kelas;
use PDF;

class AkademikController extends Controller
{
    public function krsRender()
    {
        $user = Auth::guard('mahasiswa')->user(); abort_unless($user,403);
        $webs=WebSetting::first(); $currentSemester=$this->getCurrentSemester(); $krsHeader=null;
        if($currentSemester){$krsHeader=KRS::firstOrCreate(['mahasiswa_id'=>$user->id,'taka_id'=>$currentSemester->id,'semester'=>(int)($user->semester??1)],['code'=>'KRS-'.($user->numb_nim??$user->id).'-'.now()->format('YmdHis'),'status'=>'Draft','total_sks'=>0,'max_sks'=>24,'ipk_sebelumnya'=>0]);}
        $details=$krsHeader?$krsHeader->details()->with(['mataKuliah','kelas','dosen'])->whereIn('status',['Aktif','Mengulang'])->get():collect();
        $availableCourses=MataKuliah::where('prodi_id',$user->prodi_id)->with(['prasyarat','dosen1'])->orderBy('semester')->orderBy('name')->get();
        return view('private.mahasiswa.akademik.krs',['webs'=>$webs,'spref'=>$user->prefix,'menus'=>'Akademik','pages'=>'Kartu Rencana Studi (KRS)','academy'=>$webs?$webs->school_apps.' by '.$webs->school_name:'SIAKAD','currentSemester'=>$currentSemester,'krs'=>$details,'krsHeader'=>$krsHeader,'availableCourses'=>$availableCourses,'user'=>$user]);
    }

    public function cetakKrs(){ $user=Auth::guard('mahasiswa')->user(); abort_unless($user,403); $s=$this->getCurrentSemester(); $h=$s?KRS::where('mahasiswa_id',$user->id)->where('taka_id',$s->id)->with(['dosenPA','mahasiswa.programStudi.fakultas','tahunAkademik'])->first():null; $d=$h?$h->details()->with(['mataKuliah','kelas','dosen'])->whereIn('status',['Aktif','Mengulang'])->get():collect(); return PDF::loadView('private.mahasiswa.akademik.cetak-krs',['webs'=>WebSetting::first(),'mahasiswa'=>$user,'currentSemester'=>$s,'krsHeader'=>$h,'krs'=>$d])->setPaper('a4','portrait')->download('KRS-'.preg_replace('/[^A-Za-z0-9_-]+/','-',$user->name??$user->numb_nim??'mahasiswa').'.pdf'); }

    public function khs(){ $user=Auth::guard('mahasiswa')->user(); abort_unless($user,403); $w=WebSetting::first(); return view('private.mahasiswa.akademik.khs',['webs'=>$w,'user'=>$user,'spref'=>$user->prefix,'menus'=>'Akademik','pages'=>'Kartu Hasil Studi (KHS)','academy'=>$w?$w->school_apps.' by '.$w->school_name:'SIAKAD','khsList'=>\App\Models\Akademik\KHS::with(['tahunAkademik','nilaiSemester.mataKuliah'])->where('mahasiswa_id',$user->id)->orderBy('semester')->get()]); }

    public function cetakKhs(){ $user=Auth::guard('mahasiswa')->user(); abort_unless($user,403); $k=\App\Models\Akademik\KHS::with(['mahasiswa.programStudi.fakultas','tahunAkademik','nilaiSemester.mataKuliah'])->where('mahasiswa_id',$user->id)->where('status_generate','Published')->orderByDesc('semester')->firstOrFail(); return PDF::loadView('master.akademik.khs-print',['khs'=>$k,'webs'=>WebSetting::first(),'nilai_semester'=>$k->nilaiSemester,'dosen_pa'=>null,'kaprodi'=>$k->mahasiswa->programStudi->kaprodi??null])->setPaper('a4','portrait')->download('KHS-'.$user->numb_nim.'-Semester-'.$k->semester.'.pdf'); }

    public function storeKrs(Request $request){ $u=Auth::guard('mahasiswa')->user(); abort_unless($u,403); $v=$request->validate(['mata_kuliah_id'=>['required','integer','exists:mata_kuliahs,id'],'kelas_id'=>['nullable','integer','exists:kelas,id']]); $s=$this->getCurrentSemester(); if(!$s)return back()->with('error','Tahun akademik aktif belum tersedia.'); $c=MataKuliah::findOrFail($v['mata_kuliah_id']); if((int)$c->prodi_id!==(int)$u->prodi_id)return back()->with('error','Mata kuliah bukan bagian dari program studi Anda.'); $k=KRS::firstOrCreate(['mahasiswa_id'=>$u->id,'taka_id'=>$s->id,'semester'=>(int)($u->semester??1)],['code'=>'KRS-'.$u->id.'-'.now()->format('YmdHis'),'status'=>'Draft','total_sks'=>0,'max_sks'=>24,'ipk_sebelumnya'=>0]); if(!$k->is_editable)return back()->with('error','KRS sudah diajukan/disetujui dan tidak dapat diubah.'); if($k->details()->where('matkul_id',$c->id)->whereIn('status',['Aktif','Mengulang'])->exists())return back()->with('error','Mata kuliah tersebut sudah ada di KRS.'); if(!$k->canAddMatakuliah((int)$c->sks))return back()->with('error','Batas maksimal SKS tidak mencukupi.'); // Jika mahasiswa tidak mengirim kelas_id dari form, gunakan kelas yang
        // sudah ditetapkan pada data mahasiswa.
        $kelas = !empty($v['kelas_id'])
            ? Kelas::find($v['kelas_id'])
            : (!empty($u->kelas_id) ? Kelas::find($u->kelas_id) : null);

        if (!$kelas) {
            return back()->with('error','Kelas mahasiswa belum ditentukan. Silakan pilih/atur kelas mahasiswa terlebih dahulu.');
        }

        if ((int) $kelas->prodi_id !== (int) $u->prodi_id || (int) $kelas->taka_id !== (int) $s->id) {
            return back()->with('error','Kelas tidak sesuai dengan program studi atau semester aktif.');
        } $existingDetail=$k->details()->withTrashed()->where('matkul_id',$c->id)->first(); if($existingDetail){ if($existingDetail->trashed()){$existingDetail->restore();} $existingDetail->update(['kelas_id'=>$kelas->id,'dosen_id'=>$c->dosen1_id,'sks'=>(int)$c->sks,'status'=>'Aktif','prasyarat_terpenuhi'=>true,'notes'=>null]); return back()->with('success','Mata kuliah yang sebelumnya dibatalkan berhasil diaktifkan kembali.'); } $k->details()->create(['code'=>'KRSDET-'.$u->id.'-'.$c->id.'-'.now()->format('YmdHisv'),'matkul_id'=>$c->id,'kelas_id'=>$kelas->id,'dosen_id'=>$c->dosen1_id,'sks'=>(int)$c->sks,'status'=>'Aktif','prasyarat_terpenuhi'=>true]); return back()->with('success','Mata kuliah berhasil ditambahkan ke KRS.'); }

    public function submitKrs(Request $request)
    {
        $u = Auth::guard('mahasiswa')->user();
        abort_unless($u, 403);

        $s = $this->getCurrentSemester();
        if (!$s) {
            return back()->with('error', 'Tahun akademik aktif belum tersedia.');
        }

        $k = KRS::where('mahasiswa_id', $u->id)
            ->where('taka_id', $s->id)
            ->where('semester', (int) ($u->semester ?? 1))
            ->first();

        if (!$k) {
            return back()->with('error', 'KRS semester aktif belum tersedia.');
        }

        if (!$k->is_editable) {
            return back()->with('error', 'KRS sudah diajukan atau disetujui dan tidak dapat disubmit kembali.');
        }

        $jumlahMatakuliah = $k->details()
            ->whereIn('status', ['Aktif', 'Mengulang'])
            ->count();

        if ($jumlahMatakuliah === 0 || (int) $k->total_sks <= 0) {
            return back()->with('error', 'KRS belum memiliki mata kuliah. Tambahkan mata kuliah terlebih dahulu.');
        }

        $k->submit();

        return back()->with('success', 'KRS berhasil disubmit dan menunggu persetujuan akademik.');
    }

    public function destroyKrs($id){ $u=Auth::guard('mahasiswa')->user(); abort_unless($u,403); $d=\App\Models\Akademik\KrsDetail::where('id',$id)->whereHas('krs',fn($q)=>$q->where('mahasiswa_id',$u->id))->firstOrFail(); $d->batalkan('Dibatalkan oleh mahasiswa'); return back()->with('success','Mata kuliah berhasil dibatalkan dari KRS.'); }

    public function jadwalKuliah(){ try{$u=Auth::guard('mahasiswa')->user(); abort_unless($u,403); $s=$this->getCurrentSemester(); if(!$s)return back()->with('error','Tahun akademik aktif belum tersedia.'); $a=$this->getAvailableSemesters($u); return view('private.mahasiswa.akademik.jadwal-kuliah',['menus'=>'Akademik','pages'=>'Jadwal Kuliah','user'=>$u,'spref'=>$u->prefix,'currentSemester'=>$s,'availableSemesters'=>$a,'semesters'=>$a,'jadwal'=>$this->getJadwalKuliah($u->id,$s->id),'isCurrentSemester'=>true,'webs'=>WebSetting::first(),'academy'=>'SIAKAD']);}catch(\Exception $e){return redirect()->route('mahasiswa.dashboard-render')->with('error','Terjadi kesalahan saat memuat jadwal kuliah: '.$e->getMessage());} }

    public function jadwalBySemester($id){ try{$u=Auth::guard('mahasiswa')->user(); abort_unless($u,403); if(!is_numeric($id)||$id<=0)return redirect()->route('mahasiswa.akademik.jadwal')->with('error','ID semester tidak valid'); $s=TahunAkademik::findOrFail($id); $a=$this->getAvailableSemesters($u); $cur=$this->getCurrentSemester(); return view('private.mahasiswa.akademik.jadwal-kuliah',['menus'=>'Akademik','pages'=>'Jadwal Kuliah '.$s->name,'user'=>$u,'spref'=>$u->prefix,'currentSemester'=>$s,'availableSemesters'=>$a,'semesters'=>$a,'jadwal'=>$this->getJadwalKuliah($u->id,$s->id),'isCurrentSemester'=>$cur&&$s->id==$cur->id,'webs'=>WebSetting::first(),'academy'=>'SIAKAD']);}catch(\Exception $e){return redirect()->route('mahasiswa.akademik.jadwal')->with('error','Terjadi kesalahan saat memuat jadwal: '.$e->getMessage());} }

    public function presensi(){ $u=Auth::guard('mahasiswa')->user(); abort_unless($u,403); $s=$this->getCurrentSemester(); $summary=[]; if($s){$k=KRS::where('mahasiswa_id',$u->id)->where('taka_id',$s->id)->with('details.mataKuliah')->first(); foreach(($k?->details??collect())->whereIn('status',['Aktif','Mengulang']) as $d){if($d->mataKuliah)$summary[$d->mataKuliah->id]=['mata_kuliah'=>$d->mataKuliah->nama??$d->mataKuliah->name,'kode_mk'=>$d->mataKuliah->kode_mk??$d->mataKuliah->code,'total'=>0,'hadir'=>0,'izin'=>0,'sakit'=>0,'alpha'=>0,'persentase'=>0];}} return view('private.mahasiswa.akademik.presensi',['menus'=>'Akademik','pages'=>'Presensi Mahasiswa','user'=>$u,'spref'=>$u->prefix,'currentSemester'=>$s,'summary'=>$summary,'webs'=>WebSetting::first(),'academy'=>'SIAKAD']); }

    public function detailPresensi($kode){ $u=Auth::guard('mahasiswa')->user(); abort_unless($u,403); $mk=MataKuliah::where('code',$kode)->first(); if(!$mk)return redirect()->route('mahasiswa.akademik.presensi')->with('error','Mata kuliah tidak ditemukan'); return view('private.mahasiswa.akademik.detail-presensi',['menus'=>'Akademik','pages'=>'Detail Presensi '.($mk->name??$kode),'user'=>$u,'spref'=>$u->prefix,'presensi'=>collect(),'attendances'=>collect(),'mataKuliah'=>$mk,'currentSemester'=>$this->getCurrentSemester(),'webs'=>WebSetting::first(),'academy'=>'SIAKAD']); }

    public function nilai(){ try{$u=Auth::guard('mahasiswa')->user(); abort_unless($u,403); $sem=$this->getNilaiSemester($u->id); $ipk=$this->hitungIPK($sem); return view('private.mahasiswa.akademik.nilai',['menus'=>'Akademik','pages'=>'Nilai & IPK','user'=>$u,'spref'=>$u->prefix,'semesters'=>$sem,'ipk'=>number_format($ipk['ipk'],2),'totalSks'=>$ipk['totalSks'],'availableSemesters'=>$this->getAvailableNilaiSemesters($u->id),'currentSemester'=>$this->getCurrentSemester(),'isAllSemesters'=>true,'webs'=>WebSetting::first(),'academy'=>'SIAKAD']);}catch(\Exception $e){return redirect()->route('mahasiswa.dashboard-render')->with('error','Terjadi kesalahan saat memuat nilai: '.$e->getMessage());} }

    public function nilaiBySemester($id){ try{$u=Auth::guard('mahasiswa')->user(); abort_unless($u,403); $s=TahunAkademik::findOrFail($id); $nilai=Nilai::where('mahasiswa_id',$u->id)->where('taka_id',$id)->with('mataKuliah','tahunAkademik')->orderBy('matkul_id')->get(); $ips=$this->hitungIPS($nilai); return view('private.mahasiswa.akademik.nilai-semester',['menus'=>'Akademik','pages'=>'Nilai '.$s->name,'user'=>$u,'spref'=>$u->prefix,'nilai'=>$nilai,'semester'=>$s,'ips'=>number_format($ips['ips'],2),'totalSks'=>$ips['totalSks'],'availableSemesters'=>$this->getAvailableNilaiSemesters($u->id),'currentSemester'=>$this->getCurrentSemester(),'isAllSemesters'=>false,'webs'=>WebSetting::first(),'academy'=>'SIAKAD']);}catch(\Exception $e){return redirect()->route('mahasiswa.akademik.nilai')->with('error','Terjadi kesalahan saat memuat nilai: '.$e->getMessage());} }

    private function getCurrentSemester(){ $now=now(); return TahunAkademik::where('status','Aktif')->where('start_date','<=',$now)->where('ended_date','>=',$now)->first()??TahunAkademik::latest('start_date')->first(); }

    private function getJadwalKuliah($mahasiswaId,$tahunAkademikId){
        $krs=KRS::where('mahasiswa_id',$mahasiswaId)->where('taka_id',$tahunAkademikId)->with('details.kelas')->first();
        $kelasIds=($krs?->details??collect())->whereIn('status',['Aktif','Mengulang'])->pluck('kelas_id')->filter()->unique();
        if($kelasIds->isEmpty())return collect();
        $map=['senin'=>'Monday','monday'=>'Monday','selasa'=>'Tuesday','tuesday'=>'Tuesday','rabu'=>'Wednesday','wednesday'=>'Wednesday','kamis'=>'Thursday','thursday'=>'Thursday','jumat'=>'Friday','jum\'at'=>'Friday','friday'=>'Friday','sabtu'=>'Saturday','saturday'=>'Saturday','minggu'=>'Sunday','sunday'=>'Sunday'];
        return JadwalKuliah::whereHas('kelas',fn($q)=>$q->whereIn('kelas.id',$kelasIds))->with(['mataKuliah','dosen','ruang','waktuKuliah','kelas'])->orderBy('hari')->orderBy('waktu_kuliah_id')->get()->map(function($j)use($map){$j->normalized_day=$map[strtolower(trim((string)$j->hari))]??$j->hari;return $j;})->groupBy('normalized_day');
    }

    private function getAvailableSemesters($u){return TahunAkademik::whereHas('krs',fn($q)=>$q->where('mahasiswa_id',$u->id))->orderBy('start_date','desc')->get();}
    private function getAvailableNilaiSemesters($id){return TahunAkademik::whereHas('nilai',fn($q)=>$q->where('mahasiswa_id',$id))->orderBy('start_date','desc')->get();}
    private function getNilaiSemester($id){return Nilai::where('mahasiswa_id',$id)->with(['mataKuliah','tahunAkademik'])->orderBy('taka_id','desc')->orderBy('matkul_id')->get()->groupBy('taka_id');}
    private function hitungIPK($sem){$s=0;$n=0;foreach($sem as $items)foreach($items as $v)if($v->mataKuliah&&$v->status!=='Draft'){$b=$this->hitungBobotNilai($v->nilai_angka);$s+=(float)($v->mataKuliah->sks??$v->mataKuliah->bsks??0);$n+=$b*(float)($v->mataKuliah->sks??$v->mataKuliah->bsks??0);}return ['ipk'=>$s?$n/$s:0,'totalSks'=>$s];}
    private function hitungIPS($items){return $this->hitungIPK(collect(['x'=>$items]));}
    private function hitungBobotNilai($n){if($n>=85)return 4;if($n>=80)return 3.7;if($n>=75)return 3.3;if($n>=70)return 3;if($n>=65)return 2.7;if($n>=60)return 2.3;if($n>=55)return 2;if($n>=50)return 1.7;if($n>=45)return 1;return 0;}
}