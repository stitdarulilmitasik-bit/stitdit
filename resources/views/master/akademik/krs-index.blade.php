@extends('core-themes.core-backpage')

@section('custom-css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<style>
.bg-light-primary{background-color:rgba(67,94,190,.1)}.bg-light-success{background-color:rgba(40,167,69,.1)}.bg-light-warning{background-color:rgba(255,193,7,.1)}.bg-light-info{background-color:rgba(23,162,184,.1)}.bg-light-danger{background-color:rgba(220,53,69,.1)}
.card{border:none;box-shadow:0 0 10px rgba(0,0,0,.05);border-radius:10px}.card-header{background:none;border-bottom:1px solid rgba(0,0,0,.05);padding:1.5rem}.card-body{padding:1.5rem}
.table{margin-bottom:0;min-width:860px}.table thead th{border-top:none;border-bottom:2px solid rgba(0,0,0,.05);font-weight:600;color:#6c757d;padding-top:1rem;padding-bottom:.75rem;text-align:left}.table td{vertical-align:middle;padding-top:.75rem;padding-bottom:.75rem;text-align:left}.table th.text-center,.table td.text-center{text-align:center!important}.krs-table-wrap{width:100%;overflow-x:auto;-webkit-overflow-scrolling:touch}.krs-table-wrap::-webkit-scrollbar{height:7px}.krs-table-wrap table{min-width:860px}.krs-mobile-note{display:none}
.btn{padding:.5rem 1rem;border-radius:5px}.btn-sm{padding:.25rem .5rem}.form-control,.form-select{border-radius:5px;border:1px solid rgba(0,0,0,.1);padding:.5rem 1rem}.form-control:focus,.form-select:focus{border-color:#435ebe;box-shadow:0 0 0 .2rem rgba(67,94,190,.25)}
.badge{padding:.5em .75em;font-weight:500}.collapse{transition:all .3s ease}.collapse.show{margin-top:1rem}
.copy-target-list{max-height:240px;overflow-y:auto;border:1px solid #e5e7eb;border-radius:8px}.bulk-copy-modal .modal-content{max-height:82vh}.bulk-copy-modal .modal-header{padding:.65rem 1rem}.bulk-copy-modal .modal-body{padding:.75rem 1rem}.bulk-copy-modal .modal-footer{padding:.5rem 1rem;gap:.5rem;position:sticky;bottom:0;background:#fff;border-top:1px solid #e9ecef;z-index:2}.bulk-copy-modal .modal-footer .btn{padding:.4rem .75rem}.bulk-copy-modal .alert{padding:.6rem .75rem;margin-bottom:.75rem}.bulk-copy-modal .copy-target-item{padding:.45rem .65rem}.copy-target-item{padding:.65rem .85rem;border-bottom:1px solid #f0f0f0}.copy-target-item:last-child{border-bottom:0}.copy-target-item:hover{background:#f8fafc}
@media(max-width:768px){.card-header{padding:1rem}.card-header>.d-flex{width:100%;margin-top:.75rem}.card-header>.d-flex .btn{flex:1 1 calc(50% - .5rem);min-width:140px}.card-body{padding:1rem}.krs-mobile-note{display:block;font-size:.75rem;color:#6c757d;margin-bottom:.5rem}.krs-table-wrap{margin:0 -1rem;width:calc(100% + 2rem);padding:0 1rem}.krs-table-wrap .table{min-width:860px}.table thead{display:table-header-group}.table tbody{display:table-row-group}.table tr{display:table-row;border:0}.table th,.table td{display:table-cell;white-space:nowrap}.table td{padding:.65rem .55rem}.table td:first-child,.table th:first-child{width:42px}.table td:nth-child(2){white-space:normal;min-width:190px}.table td:nth-child(8){min-width:150px}.table .btn-group{display:inline-flex;flex-wrap:nowrap}.table .btn{flex:0 0 auto}.row.mb-3{margin-left:0;margin-right:0}.row.mb-3>[class*="col-"]{margin-bottom:.5rem}.row.mb-3 .form-control,.row.mb-3 .form-select{width:100%}}
</style>
@endsection

@section('content')
<div class="row">
<div class="col-lg-8 col-12 mb-2">
<div class="card">
<div class="card-header d-flex justify-content-between align-items-center">
<h5 class="mb-0">{{ $pages }}</h5>
<div class="d-flex gap-2 flex-wrap">
<button class="btn btn-outline-primary btn-sm" type="button" onclick="openBulkCopyModal()"><i class="fas fa-copy me-2"></i>Copy Bulk KRS</button>
<button class="btn btn-success btn-sm" onclick="bulkAction('approve')"><i class="fas fa-check-circle me-2"></i>Approve Terpilih</button>
<button class="btn btn-dark btn-sm" onclick="bulkAction('publish')"><i class="fas fa-lock me-2"></i>Kunci Terpilih</button>
<button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseForm" aria-expanded="false" aria-controls="collapseForm"><i class="fas fa-plus-circle me-2"></i>Tambah KRS</button>
</div>
</div>
<div class="card-body">
<div class="row mb-4">
<div class="col-lg-2 col-md-4 mb-2"><div class="p-3 bg-light-primary rounded"><h6 class="mb-2">Total KRS</h6><h3 class="mb-0">{{ count($krs_list) }}</h3></div></div>
<div class="col-lg-2 col-md-4 mb-2"><div class="p-3 bg-light-warning rounded"><h6 class="mb-2">Dibuat</h6><h3 class="mb-0">{{ $krs_list->where('status','Draft')->count() }}</h3></div></div>
<div class="col-lg-2 col-md-4 mb-2"><div class="p-3 bg-light-info rounded"><h6 class="mb-2">Diajukan</h6><h3 class="mb-0">{{ $krs_list->where('status','Diajukan')->count() }}</h3></div></div>
<div class="col-lg-2 col-md-4 mb-2"><div class="p-3 bg-light-success rounded"><h6 class="mb-2">Disetujui</h6><h3 class="mb-0">{{ $krs_list->where('status','Disetujui')->count() }}</h3></div></div>
<div class="col-lg-2 col-md-4 mb-2"><div class="p-3 bg-light-danger rounded"><h6 class="mb-2">Ditolak</h6><h3 class="mb-0">{{ $krs_list->where('status','Ditolak')->count() }}</h3></div></div>
<div class="col-lg-2 col-md-4 mb-2"><div class="p-3 bg-light-success rounded"><h6 class="mb-2">Dipublish</h6><h3 class="mb-0">{{ $krs_list->where('status','Dicetak')->count() }}</h3></div></div>
</div>

<div class="collapse" id="collapseForm">
<div class="card card-body border">
<h5 class="card-title mb-3">Tambah KRS Baru</h5>
<form action="{{ route($spref . 'akademik.krs-handle') }}" method="post">@csrf
<div class="row">
<div class="col-md-6 mb-3"><label for="mahasiswa_id" class="form-label">Mahasiswa</label><select class="form-select" name="mahasiswa_id" id="mahasiswa_id" required><option value="">Pilih Mahasiswa</option>@foreach($mahasiswa as $m)<option value="{{ $m->id }}">{{ $m->numb_nim }} - {{ $m->name }}</option>@endforeach</select></div>
<div class="col-md-6 mb-3"><label for="tahun_akademik_id" class="form-label">Tahun Akademik</label><select class="form-select" name="tahun_akademik_id" id="tahun_akademik_id" required><option value="">Pilih Tahun Akademik</option>@foreach($tahun_akademik as $ta)<option value="{{ $ta->id }}" {{ $ta->status == 'Aktif' ? 'selected' : '' }}>{{ $ta->name }}</option>@endforeach</select></div>
<div class="col-md-6 mb-3"><label for="semester" class="form-label">Semester</label><select class="form-select" name="semester" id="semester" required><option value="">Pilih Semester</option>@for($i=1;$i<=8;$i++)<option value="{{ $i }}">Semester {{ $i }}</option>@endfor</select></div>
<div class="col-md-6 mb-3"><label for="dosen_wali_id" class="form-label">Dosen Wali (Opsional)</label><select class="form-select" name="dosen_wali_id" id="dosen_wali_id"><option value="">Pilih Dosen Wali</option>@foreach($dosens as $dosen)<option value="{{ $dosen->id }}">{{ $dosen->numb_nidn ?? 'NIDN Tidak Tersedia' }} - {{ $dosen->name }}</option>@endforeach</select></div>
<div class="col-12 mb-3"><label for="catatan" class="form-label">Catatan</label><textarea class="form-control" name="catatan" id="catatan" rows="3" placeholder="Catatan untuk KRS..."></textarea></div>
</div>
<div class="d-flex justify-content-end gap-2"><button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#collapseForm"><i class="fas fa-times me-2"></i>Batal</button><button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Simpan KRS</button></div>
</form>
</div>
</div>

<div class="row mb-3">
<div class="col-md-3"><select class="form-select" id="filterTahunAkademik" onchange="filterTable()"><option value="">Semua Tahun Akademik</option>@foreach($tahun_akademik as $ta)<option value="{{ $ta->name }}">{{ $ta->name }} - {{ $ta->semester }}</option>@endforeach</select></div>
<div class="col-md-3"><select class="form-select" id="filterStatus" onchange="filterTable()"><option value="">Semua Status</option><option value="Draft">Dibuat</option><option value="Diajukan">Diajukan</option><option value="Disetujui">Disetujui</option><option value="Ditolak">Ditolak</option><option value="Dicetak">Dipublish</option><option value="Dikunci">Dikunci</option></select></div>
<div class="col-md-3"><select class="form-select" id="filterSemester" onchange="filterTable()"><option value="">Semua Semester</option>@for($i=1;$i<=8;$i++)<option value="{{ $i }}">Semester {{ $i }}</option>@endfor</select></div>
<div class="col-md-3"><input type="text" class="form-control" id="searchInput" placeholder="Cari mahasiswa..." onkeyup="filterTable()"></div>
</div>

<div class="krs-mobile-note"><i class="fas fa-arrows-left-right me-1"></i>Geser tabel ke kiri/kanan untuk melihat semua kolom.</div>
<div class="krs-table-wrap">
<table class="table table-hover" id="krsTable">
<thead><tr><th class="text-center"><input type="checkbox" id="selectAll" onchange="toggleSelectAll()"></th><th>Mahasiswa</th><th>Tahun Akademik</th><th class="text-center">Semester</th><th class="text-center">Total SKS</th><th class="text-center">Status</th><th>Tanggal Dibuat</th><th class="text-center">Aksi</th></tr></thead>
<tbody>
@forelse($krs_list as $krs)
@php
    // Relasi dapat bernilai null jika data master mahasiswa/tahun akademik
    // sudah dihapus atau terdapat data KRS lama yang orphan. Jangan biarkan
    // satu record rusak membuat seluruh halaman KRS gagal dirender.
    $krsMahasiswa = $krs->mahasiswa;
    $krsTahunAkademik = $krs->tahunAkademik;
    $status = $krs->status ?? 'Draft';
@endphp
<tr>
<td class="text-center" data-label="Pilih"><input type="checkbox" class="krs-checkbox" value="{{ $krs->code }}"></td>
<td data-label="Mahasiswa">
<div class="d-flex flex-column">
<strong>{{ $krsMahasiswa?->name ?? 'Mahasiswa tidak ditemukan' }}</strong>
<small class="text-muted">{{ $krsMahasiswa?->numb_nim ?? ('ID: ' . ($krs->mahasiswa_id ?? '-')) }}</small>
</div>
</td>
<td data-label="Tahun Akademik">
{{ $krsTahunAkademik?->name ?? 'Tahun akademik tidak ditemukan' }}
@if(!$krsTahunAkademik)
<small class="d-block text-danger">ID: {{ $krs->taka_id ?? '-' }}</small>
@endif
</td>
<td class="text-center" data-label="Semester">{{ $krs->semester }}</td>
<td class="text-center" data-label="Total SKS"><span class="badge bg-info">{{ $krs->total_sks }} SKS</span></td>
<td class="text-center" data-label="Status">
@php $statusColors=['Draft'=>'secondary','Diajukan'=>'warning','Disetujui'=>'success','Ditolak'=>'danger','Dicetak'=>'primary','Dikunci'=>'dark'];
$statusLabels=['Draft'=>'Draft','Diajukan'=>'Diajukan','Disetujui'=>'Disetujui','Ditolak'=>'Ditolak','Dicetak'=>'Dicetak','Dikunci'=>'Dikunci']; @endphp
<span class="badge bg-{{ $statusColors[$krs->status] ?? 'secondary' }}">{{ $statusLabels[$krs->status] ?? $krs->status }}</span>
</td>
<td data-label="Tanggal Dibuat">{{ $krs->created_at->format('d/m/Y H:i') }}</td>
<td class="text-center" data-label="Aksi"><div class="btn-group" role="group">
<a href="{{ route($spref . 'akademik.krs-detail',$krs->code) }}" class="btn btn-sm btn-info" title="Detail"><i class="fas fa-eye"></i></a>
@if(in_array($krs->status,['Draft','Ditolak']))<button class="btn btn-sm btn-warning" onclick="editKRS('{{ $krs->code }}')" title="Edit"><i class="fas fa-edit"></i></button>@endif
@if($krs->status=='Diajukan')<button class="btn btn-sm btn-success" onclick="approveKRS('{{ $krs->code }}')" title="Setujui"><i class="fas fa-check"></i></button><button class="btn btn-sm btn-danger" onclick="rejectKRS('{{ $krs->code }}')" title="Tolak"><i class="fas fa-times"></i></button>@endif
@if($krs->status=='Disetujui')<button class="btn btn-sm btn-dark" onclick="lockKRS('{{ $krs->code }}')" title="Kunci"><i class="fas fa-lock"></i></button>@endif
@if(in_array($krs->status,['Disetujui','Dikunci','Dicetak']))<a href="{{ route($spref . 'akademik.krs-print',$krs->code) }}" class="btn btn-sm btn-secondary" target="_blank" title="Cetak KRS"><i class="fas fa-print"></i></a>@endif
@if(in_array($krs->status,['Draft','Ditolak']))<button class="btn btn-sm btn-danger" onclick="deleteKRS('{{ $krs->code }}')" title="Hapus"><i class="fas fa-trash"></i></button>@endif
</div></td>
</tr>
@empty
<tr>
<td colspan="8" class="text-center py-4 text-muted">Belum ada data KRS.</td>
</tr>
@endforelse
</tbody>
</table>
</div>
</div>
</div>
</div>

<div class="col-lg-4 col-12">
<div class="card"><div class="card-header"><h6 class="mb-0">Informasi KRS</h6></div><div class="card-body">
<div class="row">
<div class="col-12 mb-3"><h6 class="text-muted">Status Workflow</h6><ol class="list-group list-group-numbered">
<li class="list-group-item d-flex justify-content-between align-items-start"><div class="ms-2 me-auto"><div class="fw-bold">Draft</div>KRS dalam tahap penyusunan</div><span class="badge bg-secondary rounded-pill">{{ $krs_list->where('status','Draft')->count() }}</span></li>
<li class="list-group-item d-flex justify-content-between align-items-start"><div class="ms-2 me-auto"><div class="fw-bold">Diajukan</div>Menunggu persetujuan</div><span class="badge bg-warning rounded-pill">{{ $krs_list->where('status','Diajukan')->count() }}</span></li>
<li class="list-group-item d-flex justify-content-between align-items-start"><div class="ms-2 me-auto"><div class="fw-bold">Disetujui</div>Siap untuk dikunci</div><span class="badge bg-success rounded-pill">{{ $krs_list->where('status','Disetujui')->count() }}</span></li>
<li class="list-group-item d-flex justify-content-between align-items-start"><div class="ms-2 me-auto"><div class="fw-bold">Dikunci</div>KRS siap dicetak</div><span class="badge bg-primary rounded-pill">{{ $krs_list->where('status','Dicetak')->count() }}</span></li>
</ol></div>
<div class="col-12 mb-3"><h6 class="text-muted">Statistik Semester</h6>@for($sem=1;$sem<=8;$sem++)@php $count=$krs_list->where('semester',$sem)->count(); @endphp @if($count>0)<div class="d-flex justify-content-between align-items-center mb-1"><span>Semester {{ $sem }}</span><span class="badge bg-primary">{{ $count }}</span></div>@endif @endfor</div>
<div class="col-12"><h6 class="text-muted">Aksi Bulk</h6><div class="d-grid gap-2"><button class="btn btn-outline-primary btn-sm" onclick="openBulkCopyModal()"><i class="fas fa-copy me-2"></i>Copy Bulk KRS</button><button class="btn btn-outline-success btn-sm" onclick="bulkAction('approve')"><i class="fas fa-check-circle me-2"></i>Approve Terpilih</button><button class="btn btn-outline-dark btn-sm" onclick="bulkAction('publish')"><i class="fas fa-lock me-2"></i>Kunci Terpilih</button></div></div>
</div></div></div>
</div>
</div>

<div class="modal fade bulk-copy-modal" id="bulkCopyModal" tabindex="-1" aria-labelledby="bulkCopyModalLabel" aria-hidden="true">
<div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content">
<form method="POST" action="{{ route($spref . 'akademik.krs-copy-bulk') }}" id="bulkCopyForm">@csrf
<div class="modal-header"><h5 class="modal-title" id="bulkCopyModalLabel"><i class="fas fa-copy me-2"></i>Copy Bulk KRS sebagai Template</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
<div class="modal-body">
<div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>Pilih satu atau beberapa KRS sebagai template, lalu pilih mahasiswa tujuan. Mata kuliah yang sudah ada tidak akan diduplikasi. KRS tujuan yang sudah diajukan, disetujui, dipublish, atau dikunci tidak akan diubah.</div>
<div id="selectedSourceContainer"></div>
<div class="mb-3"><label class="form-label fw-bold">Mahasiswa Tujuan</label><input type="text" id="copyTargetSearch" class="form-control mb-2" placeholder="Cari NIM atau nama mahasiswa..." oninput="filterCopyTargets()">
<div class="copy-target-list">
@foreach($mahasiswa as $m)
<label class="copy-target-item d-flex align-items-center gap-2 copy-target-row" data-search="{{ strtolower(($m->numb_nim ?? '').' '.($m->name ?? '')) }}"><input type="checkbox" class="copy-target-checkbox" value="{{ $m->id }}" data-name="{{ $m->name }}"><span><strong>{{ $m->numb_nim ?? '-' }}</strong> - {{ $m->name ?? 'Nama tidak tersedia' }}</span></label>
@endforeach
</div></div>
<div class="d-flex justify-content-between align-items-center"><span class="text-muted small" id="copySelectionSummary">0 KRS sumber · 0 mahasiswa tujuan</span><button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleCopyTargets()">Pilih/Batal Semua Mahasiswa</button></div>
</div>
<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary"><i class="fas fa-copy me-2"></i>Salin KRS ke Mahasiswa Terpilih</button></div>
</form>
</div></div></div>
@endsection

@section('custom-js')
<script>
function filterTable(){
const tahunAkademik=document.getElementById('filterTahunAkademik').value.toLowerCase(),status=document.getElementById('filterStatus').value.toLowerCase(),semester=document.getElementById('filterSemester').value,search=document.getElementById('searchInput').value.toLowerCase(),rows=document.getElementById('krsTable').getElementsByTagName('tbody')[0].getElementsByTagName('tr');
for(let i=0;i<rows.length;i++){const row=rows[i],mahasiswaText=row.cells[1].textContent.toLowerCase(),tahunAkademikText=row.cells[2].textContent.toLowerCase(),semesterText=row.cells[3].textContent,statusText=row.cells[5].textContent.toLowerCase();let show=true;if(tahunAkademik&&!tahunAkademikText.includes(tahunAkademik))show=false;if(status&&!statusText.includes(status))show=false;if(semester&&semesterText!==semester)show=false;if(search&&!mahasiswaText.includes(search))show=false;row.style.display=show?'':'none';}
}
function toggleSelectAll(){document.querySelectorAll('.krs-checkbox').forEach(c=>c.checked=document.getElementById('selectAll').checked);}
function openBulkCopyModal(){
const sources=Array.from(document.querySelectorAll('.krs-checkbox:checked')).map(c=>c.value);
if(!sources.length){alert('Pilih minimal satu KRS sumber sebagai template terlebih dahulu.');return;}
const container=document.getElementById('selectedSourceContainer');container.innerHTML='';
sources.forEach(code=>{const input=document.createElement('input');input.type='hidden';input.name='source_codes[]';input.value=code;container.appendChild(input);});
updateCopySummary();
const modalEl=document.getElementById('bulkCopyModal');
if(window.bootstrap&&bootstrap.Modal){bootstrap.Modal.getOrCreateInstance(modalEl).show();}else{modalEl.classList.add('show');modalEl.style.display='block';}
}
function filterCopyTargets(){
const q=document.getElementById('copyTargetSearch').value.toLowerCase();
document.querySelectorAll('.copy-target-row').forEach(row=>row.style.display=row.dataset.search.includes(q)?'flex':'none');
}
function toggleCopyTargets(){
const visible=document.querySelectorAll('.copy-target-row:not([style*="display: none"]) .copy-target-checkbox');
const shouldCheck=Array.from(visible).some(c=>!c.checked);visible.forEach(c=>c.checked=shouldCheck);updateCopySummary();
}
function updateCopySummary(){document.getElementById('copySelectionSummary').textContent=document.querySelectorAll('#selectedSourceContainer input[name="source_codes[]"]').length+' KRS sumber · '+document.querySelectorAll('.copy-target-checkbox:checked').length+' mahasiswa tujuan';}
document.addEventListener('change',function(e){if(e.target.classList.contains('copy-target-checkbox'))updateCopySummary();});
document.getElementById('bulkCopyForm').addEventListener('submit',function(e){
const targets=Array.from(document.querySelectorAll('.copy-target-checkbox:checked')).map(c=>c.value);
if(!targets.length){e.preventDefault();alert('Pilih minimal satu mahasiswa tujuan.');return;}
targets.forEach(id=>{const input=document.createElement('input');input.type='hidden';input.name='target_mahasiswa_ids[]';input.value=id;this.appendChild(input);});
if(!confirm('Salin KRS dari template terpilih ke '+targets.length+' mahasiswa?'))e.preventDefault();
});
function bulkAction(action){
const codes=Array.from(document.querySelectorAll('.krs-checkbox:checked')).map(cb=>cb.value);if(!codes.length){alert('Pilih minimal satu KRS untuk diproses!');return;}
const actionText=action==='approve'?'menyetujui':'mengunci';if(!confirm('Apakah Anda yakin ingin '+actionText+' '+codes.length+' KRS yang dipilih?'))return;
const form=document.createElement('form');form.method='POST';form.action=action==='approve'?'{{ route($spref."akademik.krs-bulk-approve") }}':'{{ route($spref."akademik.krs-bulk-publish") }}';addCsrf(form);codes.forEach(code=>addHidden(form,'codes[]',code));document.body.appendChild(form);form.submit();
}
function addCsrf(form){addHidden(form,'_token','{{ csrf_token() }}');}
function addHidden(form,name,value){const input=document.createElement('input');input.type='hidden';input.name=name;input.value=value;form.appendChild(input);}
function approveKRS(code){submitAction('{{ route($spref."akademik.krs-approve",":code") }}'.replace(':code',code),'Apakah Anda yakin ingin menyetujui KRS ini?');}
function publishKRS(code){submitAction('{{ route($spref."akademik.krs-publish",":code") }}'.replace(':code',code),'Apakah Anda yakin ingin mengunci KRS ini?');}
function lockKRS(code){submitAction('{{ route($spref."akademik.krs-lock",":code") }}'.replace(':code',code),'Apakah Anda yakin ingin mengunci KRS ini? KRS yang dikunci tidak dapat diubah lagi.');}
function rejectKRS(code){const reason=prompt('Masukkan alasan penolakan:');if(!reason)return;const form=document.createElement('form');form.method='POST';form.action='{{ route($spref."akademik.krs-reject",":code") }}'.replace(':code',code);addCsrf(form);addHidden(form,'reason',reason);addHidden(form,'notes',reason);document.body.appendChild(form);form.submit();}
function deleteKRS(code){if(!confirm('Apakah Anda yakin ingin menghapus KRS ini?'))return;const form=document.createElement('form');form.method='POST';form.action='{{ route($spref."akademik.krs-delete",":code") }}'.replace(':code',code);addCsrf(form);addHidden(form,'_method','DELETE');document.body.appendChild(form);form.submit();}
function submitAction(url,message){if(!confirm(message))return;const form=document.createElement('form');form.method='POST';form.action=url;addCsrf(form);document.body.appendChild(form);form.submit();}
function editKRS(code){window.location.href='{{ route($spref."akademik.krs-detail",":code") }}'.replace(':code',code);}
</script>
@endsection
