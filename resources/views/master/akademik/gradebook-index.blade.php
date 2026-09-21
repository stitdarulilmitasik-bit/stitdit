@extends('core-themes.core-backpage')

@section('custom-css')
<style>
.nilai-page{--soft:#f4f7f6;--line:#e4ebe8}
.nilai-filter{background:linear-gradient(135deg,#f2f8f5,#fff);border:1px solid var(--line);border-radius:12px}
.nilai-title{font-weight:700;letter-spacing:-.2px}.nilai-sub{font-size:12px;color:#74817c}
.filter-label{font-size:11px;font-weight:600;color:#66736e;margin-bottom:4px}
.nilai-filter .form-select{height:34px;font-size:12px;border-color:#dbe4e0}
.nilai-table-wrap{overflow:auto;max-height:66vh;border:1px solid var(--line);border-radius:10px}
.gradebook{min-width:1050px;font-size:11px}.gradebook th,.gradebook td{white-space:nowrap;vertical-align:middle;padding:4px!important}
.gradebook thead th{position:sticky;top:0;z-index:4;background:#f7faf9;color:#56635e;font-size:10px;text-transform:uppercase;letter-spacing:.25px}
.sticky-no{position:sticky;left:0;z-index:3;background:#fff;width:36px}.sticky-student{position:sticky;left:36px;z-index:3;background:#fff;min-width:190px;border-right:2px solid #e6eeeb}
.score{width:52px;height:28px;text-align:center;padding:2px!important;font-size:11px}
.result-cell{font-weight:700;text-align:center;background:#fafcfb}.status-badge{font-size:9px;padding:3px 5px;min-width:62px}
.nilai-info{border:1px solid var(--line);border-radius:10px;background:#fff}.stat-pill{font-size:11px;padding:5px 9px;border-radius:20px;background:#f1f5f3;color:#52605b}
.workflow{font-size:11px;color:#6d7975}.workflow b{color:#3e4a46}
.workflow-table th{font-size:10px;text-transform:uppercase;color:#687570}.workflow-table td{font-size:11px;padding:5px!important}
@endsection

@section('content')
<div class="container-fluid nilai-page">
    <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div><h4 class="nilai-title mb-0">Input Nilai Mahasiswa</h4><div class="nilai-sub">Kelola nilai berdasarkan KRS tanpa mengubah relasi data akademik.</div></div>
            <span class="workflow"><b>Draft</b> → Ajukan → Verifikasi → Publish → Lock</span>
        </div>
        <div class="nilai-filter p-3">
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
                <div class="col-md-4">
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
                    <button class="btn btn-primary btn-sm flex-fill"><i class="bi bi-search"></i> Tampilkan</button>
                    <a href="{{ request()->url() }}" class="btn btn-outline-secondary btn-sm" title="Reset filter"><i class="bi bi-arrow-counterclockwise"></i></a>
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

    <div class="nilai-info p-3 mb-2">
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
        <div>
            @if(!$first)
                <div class="alert alert-info mb-0">Pilih tahun akademik, semester, mata kuliah, dan/atau kelas untuk membuka gradebook.</div>
            @else
                <div class="workflow mb-2"><b>Alur:</b> Draft → Ajukan → Approved → Published → Locked. Nilai Published/Locked tidak dapat diedit.</div>

                @if($isDosen)
                    <form method="POST" action="{{ route('dosen.akademik.gradebook.save') }}">
                @else
                    <form method="POST" action="{{ route('web-admin.akademik.gradebook.save') }}">
                @endif
                    @csrf
                    <div class="nilai-table-wrap">
                        <table class="table table-sm table-bordered gradebook mb-0">
                            <thead>
                                <tr>
                                    <th class="sticky-no">No.</th>
                                    <th class="sticky-student">Nama Mahasiswa</th>
                                    <th>Tugas 1</th><th>Tugas 2</th><th>Tugas 3</th>
                                    <th>Quiz 1</th><th>Quiz 2</th>
                                    <th>UTS</th><th>UAS</th><th>Praktikum</th><th>Kehadiran</th>
                                    <th>Nilai Akhir</th><th>Huruf</th><th>Mutu</th><th>Hasil</th><th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                            @php
                                // Kelompokkan baris berdasarkan mahasiswa agar nama tidak
                                // berulang ketika satu mahasiswa memiliki lebih dari satu
                                // detail KRS/nilai pada workset yang sama.
                                $studentGroups = $details
                                    ->sortBy(fn ($detail) => mb_strtolower((string) ($detail->krs->mahasiswa->name ?? '')))
                                    ->groupBy(fn ($detail) => $detail->krs->mahasiswa_id);

                                $rowIndex = 0;
                            @endphp
                            @forelse($studentGroups as $studentId => $studentDetails)
                                @foreach($studentDetails as $groupRow => $detail)
                                    @php
                                        $n = $detail->nilai;
                                        $isFirstStudentRow = $loop->first;
                                        $rowspan = $studentDetails->count();
                                        $currentIndex = $rowIndex++;
                                    @endphp
                                    <tr class="{{ $isFirstStudentRow ? 'border-top border-2' : '' }}">
                                        <td class="sticky-no text-center">{{ $currentIndex + 1 }}</td>
                                        @if($isFirstStudentRow)
                                            <td class="sticky-student" rowspan="{{ $rowspan }}">
                                                <div class="fw-semibold">{{ $detail->krs->mahasiswa->name }}</div>
                                                <div class="small text-muted">
                                                    {{ $detail->krs->mahasiswa->nim ?? $detail->krs->mahasiswa->numb_nim ?? '-' }}
                                                </div>
                                            </td>
                                        @endif
                                        <input type="hidden" name="nilai[{{ $currentIndex }}][id]" value="{{ $n->id }}">
                                        @foreach(['tugas_1','tugas_2','tugas_3','quiz_1','quiz_2','uts','uas','praktikum','kehadiran'] as $field)
                                            <td>
                                                <input class="form-control form-control-sm score"
                                                    type="number" min="0" max="100" step="0.01"
                                                    name="nilai[{{ $currentIndex }}][{{ $field }}]"
                                                    value="{{ old('nilai.'.$currentIndex.'.'.$field, $n->{$field}) }}"
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
                            @empty
                                <tr>
                                    <td colspan="16" class="text-center text-muted py-4">Belum ada data mahasiswa.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-2 d-flex justify-content-between align-items-center gap-2">
                        <div class="small text-muted">Isi komponen nilai. Perhitungan nilai akhir tetap mengikuti bobot dan relasi data yang sudah ada.</div>
                        <button class="btn btn-primary" type="submit" @disabled(!$isDosen)>
                            <i class="bi bi-save me-1"></i> Simpan Draft
                        </button>
                    </div>
                </form>

                <div class="mt-3 mb-2"><strong style="font-size:12px">Kontrol Workflow</strong></div>
                <div class="table-responsive nilai-info">
                    <table class="table table-sm align-middle workflow-table mb-0">
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
