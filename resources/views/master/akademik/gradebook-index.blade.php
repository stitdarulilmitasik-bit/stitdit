@extends('core-themes.core-backpage')

@section('custom-css')
<style>
    .nilai-page { --soft:#f4f7f6; --line:#e4ebe8; }
    .nilai-filter { background:linear-gradient(135deg,#f2f8f5,#fff); border:1px solid var(--line); border-radius:12px; }
    .nilai-title { font-weight:700; letter-spacing:-.2px; }
    .nilai-sub { font-size:12px; color:#74817c; }
    .filter-label { font-size:11px; font-weight:600; color:#66736e; margin-bottom:4px; }
    .nilai-filter .form-select { height:34px; font-size:12px; border-color:#dbe4e0; }
    .nilai-table-wrap { overflow:auto; max-height:66vh; border:1px solid var(--line); border-radius:10px; }
    .gradebook { min-width:980px; font-size:11px; }
    .gradebook th,.gradebook td { white-space:nowrap; vertical-align:middle; padding:5px !important; }
    .gradebook thead th { position:sticky; top:0; z-index:4; background:#f7faf9; color:#56635e; font-size:10px; text-transform:uppercase; letter-spacing:.25px; }
    .sticky-no { position:sticky; left:0; z-index:3; background:#fff; width:36px; }
    .sticky-student { position:sticky; left:36px; z-index:3; background:#fff; min-width:205px; border-right:2px solid #e6eeeb; }
    .score { width:64px; height:30px; text-align:center; padding:2px !important; font-size:11px; }
    .score-label { display:block; font-size:9px; color:#7a8581; margin-top:2px; }
    .auto-cell { min-width:70px; text-align:center; font-weight:700; }
    .status-badge { font-size:9px; padding:3px 5px; min-width:62px; }
    .nilai-info { border:1px solid var(--line); border-radius:10px; background:#fff; }
    .workflow { font-size:11px; color:#6d7975; }
    .workflow b { color:#3e4a46; }
    .workflow-table th { font-size:10px; text-transform:uppercase; color:#687570; }
    .workflow-table td { font-size:11px; padding:5px !important; }
</style>
@endsection

@section('content')
<div class="container-fluid nilai-page">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div>
            <h4 class="nilai-title mb-0">Input Nilai Mahasiswa</h4>
            <div class="nilai-sub">Satu input untuk setiap komponen penilaian. Nilai akhir dan huruf dihitung otomatis.</div>
        </div>
        <span class="workflow"><b>Draft</b> → Ajukan → Verifikasi → Publish → Lock</span>
    </div>

    <div class="nilai-filter p-3 mb-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="filter-label">Tahun Akademik</label>
                <select name="taka_id" class="form-select">
                    <option value="">Semua</option>
                    @foreach($takaList as $ta)
                        <option value="{{ $ta->id }}" @selected($activeTaka == $ta->id)>{{ $ta->name }} - {{ $ta->semester }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="filter-label">Semester</label>
                <select name="semester" class="form-select">
                    <option value="">Semua</option>
                    @for($s=1;$s<=14;$s++)
                        <option value="{{ $s }}" @selected($activeSemester == $s)>Semester {{ $s }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-3">
                <label class="filter-label">Mata Kuliah</label>
                <select name="matkul_id" class="form-select">
                    <option value="">Semua</option>
                    @foreach($matkulList as $mk)
                        <option value="{{ $mk->id }}" @selected($activeMatkul == $mk->id)>{{ $mk->code }} — {{ $mk->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="filter-label">Kelas</label>
                <select name="kelas_id" class="form-select">
                    <option value="">Semua</option>
                    @foreach($kelasList as $kelas)
                        <option value="{{ $kelas->id }}" @selected($activeKelas == $kelas->id)>{{ $kelas->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm flex-fill"><i class="bi bi-search"></i> Tampilkan</button>
                <a href="{{ request()->url() }}" class="btn btn-outline-secondary btn-sm" title="Reset filter"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>
    </div>

    @if(session('success'))
        <div class="alert alert-success py-2">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger py-2">{{ session('error') }}</div>
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
        $statusColors = ['Draft'=>'secondary','Submitted'=>'warning','Approved'=>'info','Published'=>'success','Locked'=>'dark'];
        $components = [
            'tugas' => ['label'=>'Tugas','weight'=>$weights['bobot_tugas']],
            'quiz' => ['label'=>'Quiz','weight'=>$weights['bobot_quiz']],
            'uts' => ['label'=>'UTS','weight'=>$weights['bobot_uts']],
            'uas' => ['label'=>'UAS','weight'=>$weights['bobot_uas']],
            'praktikum' => ['label'=>'Praktikum','weight'=>$weights['bobot_praktikum']],
            'kehadiran' => ['label'=>'Kehadiran','weight'=>$weights['bobot_kehadiran']],
        ];
    @endphp

    <div class="nilai-info p-3 mb-3 d-flex justify-content-between align-items-center">
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

    @if(!$first)
        <div class="alert alert-info mb-0">Pilih tahun akademik, semester, mata kuliah, dan/atau kelas untuk membuka gradebook.</div>
    @else
        <div class="workflow mb-2"><b>Komponen nilai:</b> Tugas {{ $weights['bobot_tugas'] }}% · Quiz {{ $weights['bobot_quiz'] }}% · UTS {{ $weights['bobot_uts'] }}% · UAS {{ $weights['bobot_uas'] }}% · Praktikum {{ $weights['bobot_praktikum'] }}% · Kehadiran {{ $weights['bobot_kehadiran'] }}%. Tidak ada input Tugas 1–3 atau Quiz 1–2.</div>

        <form method="POST" action="{{ route($isDosen ? 'dosen.akademik.gradebook.save' : 'web-admin.akademik.gradebook.save') }}">
            @csrf
            <div class="nilai-table-wrap">
                <table class="table table-sm table-bordered gradebook mb-0">
                    <thead>
                        <tr>
                            <th class="sticky-no">No.</th>
                            <th class="sticky-student">Nama Mahasiswa</th>
                            @foreach($components as $component)
                                <th>{{ $component['label'] }}<span class="score-label">{{ $component['weight'] }}%</span></th>
                            @endforeach
                            <th>Nilai Akhir</th><th>Huruf</th><th>Mutu</th><th>Hasil</th><th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    @php
                        $studentGroups = $details->sortBy(fn($d)=>mb_strtolower((string)($d->krs->mahasiswa->name ?? '')))->groupBy(fn($d)=>$d->krs->mahasiswa_id);
                        $flatIndex = 0;
                    @endphp
                    @forelse($studentGroups as $studentId=>$studentDetails)
                        @foreach($studentDetails as $detail)
                            @php $n=$detail->nilai; $index=$flatIndex++; @endphp
                            <tr @class(['border-top border-2'=>$loop->first])>
                                <td class="sticky-no text-center">{{ $index+1 }}</td>
                                @if($loop->first)
                                    <td class="sticky-student" rowspan="{{ $studentDetails->count() }}">
                                        <div class="fw-semibold">{{ $detail->krs->mahasiswa->name }}</div>
                                        <div class="small text-muted">{{ $detail->krs->mahasiswa->nim ?? $detail->krs->mahasiswa->numb_nim ?? '-' }}</div>
                                    </td>
                                @endif
                                <input type="hidden" name="nilai[{{ $index }}][id]" value="{{ $n->id }}">

                                <td><input type="number" min="0" max="100" step="0.01" class="form-control form-control-sm score" name="nilai[{{ $index }}][tugas]" value="{{ old("nilai.{$index}.tugas", $n->tugas_1) }}" @disabled(!$n->is_editable)></td>
                                <td><input type="number" min="0" max="100" step="0.01" class="form-control form-control-sm score" name="nilai[{{ $index }}][quiz]" value="{{ old("nilai.{$index}.quiz", $n->quiz_1) }}" @disabled(!$n->is_editable)></td>
                                <td><input type="number" min="0" max="100" step="0.01" class="form-control form-control-sm score" name="nilai[{{ $index }}][uts]" value="{{ old("nilai.{$index}.uts", $n->uts) }}" @disabled(!$n->is_editable)></td>
                                <td><input type="number" min="0" max="100" step="0.01" class="form-control form-control-sm score" name="nilai[{{ $index }}][uas]" value="{{ old("nilai.{$index}.uas", $n->uas) }}" @disabled(!$n->is_editable)></td>
                                <td><input type="number" min="0" max="100" step="0.01" class="form-control form-control-sm score" name="nilai[{{ $index }}][praktikum]" value="{{ old("nilai.{$index}.praktikum", $n->praktikum) }}" @disabled(!$n->is_editable)></td>
                                <td><input type="number" min="0" max="100" step="0.01" class="form-control form-control-sm score" name="nilai[{{ $index }}][kehadiran]" value="{{ old("nilai.{$index}.kehadiran", $n->kehadiran) }}" @disabled(!$n->is_editable)></td>

                                <td class="auto-cell">{{ $n->nilai_angka !== null ? number_format($n->nilai_angka,2) : '-' }}</td>
                                <td class="auto-cell">{{ $n->nilai_huruf ?? '-' }}</td>
                                <td class="auto-cell">{{ $n->nilai_mutu !== null ? number_format($n->nilai_mutu,2) : '-' }}</td>
                                <td class="text-center"><span class="badge {{ $n->is_lulus ? 'bg-success':'bg-danger' }}">{{ $n->is_lulus ? 'Lulus':'Tidak Lulus' }}</span></td>
                                <td class="text-center"><span class="badge bg-{{ $statusColors[$n->status] ?? 'secondary' }} status-badge">{{ $n->status }}</span></td>
                            </tr>
                        @endforeach
                    @empty
                        <tr><td colspan="13" class="text-center text-muted py-4">Belum ada data mahasiswa.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-2 d-flex justify-content-between align-items-center gap-2">
                <div class="small text-muted">Nilai Akhir, Nilai Huruf, Mutu, dan Hasil bukan input manual. Sistem menghitungnya otomatis dari komponen nilai.</div>
                <button class="btn btn-primary" type="submit" @disabled(!$isDosen)><i class="bi bi-save me-1"></i> Simpan Draft</button>
            </div>
        </form>

        <div class="mt-4 mb-2"><strong style="font-size:12px">Kontrol Workflow</strong></div>
        <div class="table-responsive nilai-info">
            <table class="table table-sm align-middle workflow-table mb-0">
                <thead><tr><th>Mahasiswa</th><th>Status</th><th>Aksi</th><th>Versi</th></tr></thead>
                <tbody>
                @foreach($details as $detail)
                    @php $n=$detail->nilai; @endphp
                    <tr>
                        <td>{{ $detail->krs->mahasiswa->name }}</td>
                        <td><span class="badge bg-{{ $statusColors[$n->status] ?? 'secondary' }}">{{ $n->workflow_label }}</span></td>
                        <td class="d-flex gap-1 flex-wrap">
                            @if($isDosen && $n->status==='Draft')
                                <form method="POST" action="{{ route('dosen.akademik.gradebook.submit',$n->code) }}">@csrf<button class="btn btn-warning btn-sm">Ajukan</button></form>
                            @endif
                            @if(!$isDosen)
                                @if($n->status==='Submitted')
                                    <form method="POST" action="{{ route('web-admin.akademik.gradebook.approve',$n->code) }}">@csrf<button class="btn btn-info btn-sm">Setujui</button></form>
                                @elseif($n->status==='Approved')
                                    <form method="POST" action="{{ route('web-admin.akademik.gradebook.publish',$n->code) }}">@csrf<button class="btn btn-success btn-sm">Publish</button></form>
                                @elseif($n->status==='Published')
                                    <form method="POST" action="{{ route('web-admin.akademik.gradebook.lock',$n->code) }}">@csrf<button class="btn btn-dark btn-sm">Lock</button></form>
                                @elseif($n->status==='Locked')
                                    <form method="POST" action="{{ route('web-admin.akademik.gradebook.reopen',$n->code) }}" onsubmit="return confirm('Buka kembali nilai ini? Alasan wajib dicatat untuk audit.');">
                                        @csrf<input type="hidden" name="reason" value="Koreksi nilai berdasarkan permintaan resmi/hasil verifikasi akademik."><button class="btn btn-outline-danger btn-sm">Buka untuk Koreksi</button>
                                    </form>
                                @endif
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
@endsection
