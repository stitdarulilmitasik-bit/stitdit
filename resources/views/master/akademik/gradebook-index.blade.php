@extends('core-themes.core-backpage')

@section('custom-css')
<style>
.gradebook-wrap { overflow:auto; max-height:70vh; }
.gradebook { min-width:1500px; }
.gradebook th, .gradebook td { white-space:nowrap; vertical-align:middle; }
.gradebook thead th { position:sticky; top:0; z-index:3; background:#f8f9fa; }
.sticky-no { position:sticky; left:0; z-index:2; background:#fff; }
.sticky-student { position:sticky; left:55px; z-index:2; background:#fff; min-width:240px; }
.score { width:72px; text-align:center; }
.status-badge { min-width:95px; display:inline-block; }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="card mb-3">
        <div class="card-header">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h4 class="mb-1">Gradebook Nilai</h4>
                    <div class="text-muted small">Input nilai berbasis KRS. Nilai bergerak melalui Draft → Diajukan → Disetujui → Published → Locked.</div>
                </div>
                <div class="small text-muted">Validasi, perhitungan, dan audit dilakukan di server.</div>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Tahun Akademik</label>
                    <select name="taka_id" class="form-select">
                        <option value="">Semua</option>
                        @foreach($takaList as $ta)
                            <option value="{{ $ta->id }}" @selected($activeTaka == $ta->id)>{{ $ta->name }} - {{ $ta->semester }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Semester</label>
                    <select name="semester" class="form-select">
                        <option value="">Semua</option>
                        @for($s=1;$s<=14;$s++)
                            <option value="{{ $s }}" @selected($activeSemester == $s)>Semester {{ $s }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Mata Kuliah</label>
                    <select name="matkul_id" class="form-select">
                        <option value="">Semua</option>
                        @foreach($matkulList as $mk)
                            <option value="{{ $mk->id }}" @selected($activeMatkul == $mk->id)>{{ $mk->code }} — {{ $mk->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Kelas</label>
                    <select name="kelas_id" class="form-select">
                        <option value="">Semua</option>
                        @foreach($kelasList as $kelas)
                            <option value="{{ $kelas->id }}" @selected($activeKelas == $kelas->id)>{{ $kelas->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100"><i class="bi bi-search"></i> Tampilkan</button>
                </div>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @php
        $first = $details->first();
        $sample = $first?->nilai;
        $weights = [
            'bobot_tugas' => $sample?->bobot_tugas ?? 20,
            'bobot_quiz' => $sample?->bobot_quiz ?? 10,
            'bobot_uts' => $sample?->bobot_uts ?? 25,
            'bobot_uas' => $sample?->bobot_uas ?? 30,
            'bobot_praktikum' => $sample?->bobot_praktikum ?? 0,
            'bobot_kehadiran' => $sample?->bobot_kehadiran ?? 15,
        ];
        $weightTotal = array_sum($weights);
    @endphp

    <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <strong>{{ $first?->mataKuliah?->name ?? 'Belum memilih mata kuliah' }}</strong>
                @if($first)
                    <span class="text-muted"> · {{ $first->mataKuliah->code }} · {{ $first->mataKuliah->sks }} SKS · {{ $first->kelas?->name ?? 'Tanpa kelas' }}</span>
                @endif
            </div>
            <div class="d-flex gap-2">
                <span class="badge bg-secondary">{{ $details->count() }} mahasiswa</span>
                <span class="badge {{ abs($weightTotal-100)<0.01 ? 'bg-success':'bg-danger' }}">Bobot {{ number_format($weightTotal,2) }}%</span>
            </div>
        </div>
        <div class="card-body">
            @if(!$first)
                <div class="alert alert-info mb-0">Pilih tahun akademik, semester, mata kuliah, dan/atau kelas untuk membuka gradebook.</div>
            @else
                <div class="alert alert-light border small">
                    <strong>Alur resmi:</strong>
                    Dosen mengisi dan menyimpan <b>Draft</b> → <b>Ajukan</b> → Akademik memverifikasi <b>Approved</b> → Akademik <b>Publish</b> → setelah final dapat <b>Lock</b>.
                    Nilai yang sudah Published/Locked tidak dapat diedit langsung.
                </div>

                @if($isDosen)
                    <form method="POST" action="{{ route('dosen.akademik.gradebook.save') }}">
                @else
                    <form method="POST" action="{{ route('web-admin.akademik.gradebook.save') }}">
                @endif
                    @csrf
                    <div class="gradebook-wrap border rounded">
                        <table class="table table-sm table-bordered gradebook mb-0">
                            <thead>
                                <tr>
                                    <th class="sticky-no">No.</th>
                                    <th class="sticky-student">Mahasiswa</th>
                                    <th>NIM</th>
                                    <th>Tugas 1</th><th>Tugas 2</th><th>Tugas 3</th>
                                    <th>Quiz 1</th><th>Quiz 2</th>
                                    <th>UTS</th><th>UAS</th><th>Praktikum</th><th>Kehadiran</th>
                                    <th>Nilai Akhir</th><th>Huruf</th><th>Mutu</th><th>Hasil</th><th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($details as $i => $detail)
                                @php $n = $detail->nilai; @endphp
                                <tr>
                                    <td class="sticky-no text-center">{{ $i+1 }}</td>
                                    <td class="sticky-student">
                                        <div class="fw-semibold">{{ $detail->krs->mahasiswa->name }}</div>
                                        <div class="small text-muted">{{ $detail->mataKuliah->code }} · {{ $detail->kelas?->name ?? '-' }}</div>
                                        <input type="hidden" name="nilai[{{ $i }}][id]" value="{{ $n->id }}">
                                    </td>
                                    <td>{{ $detail->krs->mahasiswa->nim ?? $detail->krs->mahasiswa->numb_nim ?? '-' }}</td>
                                    @foreach(['tugas_1','tugas_2','tugas_3','quiz_1','quiz_2','uts','uas','praktikum','kehadiran'] as $field)
                                        <td>
                                            <input class="form-control form-control-sm score"
                                                type="number" min="0" max="100" step="0.01"
                                                name="nilai[{{ $i }}][{{ $field }}]"
                                                value="{{ old('nilai.'.$i.'.'.$field, $n->{$field}) }}"
                                                @disabled(!$n->is_editable)>
                                        </td>
                                    @endforeach
                                    <td class="text-center fw-bold">{{ $n->nilai_angka !== null ? number_format($n->nilai_angka,2) : '-' }}</td>
                                    <td class="text-center fw-bold">{{ $n->nilai_huruf ?? '-' }}</td>
                                    <td class="text-center">{{ $n->nilai_mutu !== null ? number_format($n->nilai_mutu,2) : '-' }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ $n->is_lulus ? 'bg-success' : 'bg-danger' }}">
                                            {{ $n->is_lulus ? 'Lulus' : 'Tidak Lulus' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ match($n->status){'Draft'=>'secondary','Submitted'=>'warning','Approved'=>'info','Published'=>'success','Locked'=>'dark',default=>'secondary'} }} status-badge">
                                            {{ $n->status }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3 d-flex justify-content-between align-items-center">
                        <div class="small text-muted">Kosongkan komponen yang memang tidak digunakan. Komponen dengan bobot &gt; 0 wajib diisi sebelum pengajuan.</div>
                        <button class="btn btn-primary" type="submit" @disabled(!$isDosen)>
                            <i class="bi bi-save me-1"></i> Simpan Draft
                        </button>
                    </div>
                </form>

                <hr>
                <h6>Kontrol Workflow</h6>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead><tr><th>Mahasiswa</th><th>Status</th><th>Aksi</th><th>Versi</th></tr></thead>
                        <tbody>
                        @foreach($details as $detail)
                            @php $n=$detail->nilai; @endphp
                            <tr>
                                <td>{{ $detail->krs->mahasiswa->name }}</td>
                                <td><span class="badge bg-{{ match($n->status){'Draft'=>'secondary','Submitted'=>'warning','Approved'=>'info','Published'=>'success','Locked'=>'dark',default=>'secondary'} }}">{{ $n->workflow_label }}</span></td>
                                <td class="d-flex gap-1 flex-wrap">
                                    @if($isDosen && $n->status === 'Draft')
                                        <form method="POST" action="{{ route('dosen.akademik.gradebook.submit',$n->code) }}">@csrf<button class="btn btn-warning btn-sm">Ajukan</button></form>
                                    @endif
                                    @if(!$isDosen && $n->status === 'Submitted')
                                        <form method="POST" action="{{ route('web-admin.akademik.gradebook.approve',$n->code) }}">@csrf<button class="btn btn-info btn-sm">Setujui</button></form>
                                    @endif
                                    @if(!$isDosen && $n->status === 'Approved')
                                        <form method="POST" action="{{ route('web-admin.akademik.gradebook.publish',$n->code) }}">@csrf<button class="btn btn-success btn-sm">Publish</button></form>
                                    @endif
                                    @if(!$isDosen && $n->status === 'Published')
                                        <form method="POST" action="{{ route('web-admin.akademik.gradebook.lock',$n->code) }}">@csrf<button class="btn btn-dark btn-sm">Lock</button></form>
                                    @endif
                                    @if(!$isDosen && $n->status === 'Locked')
                                        <form method="POST" action="{{ route('web-admin.akademik.gradebook.reopen',$n->code) }}" onsubmit="return confirm('Buka kembali nilai ini? Alasan wajib dicatat untuk audit.');">
                                            @csrf
                                            <input type="hidden" name="reason" value="Koreksi nilai berdasarkan permintaan resmi/hasil verifikasi akademik.">
                                            <button class="btn btn-outline-danger btn-sm">Buka untuk Koreksi</button>
                                        </form>
                                    @endif
                                </td>
                                <td>{{ $n->grade_version ?? 1 }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
