@extends('core-themes.core-backpage')
@section('content')
<div class="container-xl py-3"><h2>Persetujuan KRS</h2><p class="text-muted">KRS yang memiliki mata kuliah yang Anda ampu akan muncul di sini.</p>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
<div class="row row-cards">@forelse($krs as $item)<div class="col-md-6"><div class="card"><div class="card-body"><div class="d-flex justify-content-between"><div><h3>{{ $item->mahasiswa->name ?? '-' }}</h3><div class="text-muted">{{ $item->tahunAkademik->name ?? '-' }} · Semester {{ $item->semester }}</div></div><span class="badge">{{ $item->status }}</span></div><div class="mb-3">
<details>
<summary class="btn btn-sm btn-outline-primary">Preview PDF KRS</summary>
<div class="mt-2">
<iframe src="{{ route('dosen.akademik.krs.preview', $item->code) }}" title="Preview PDF KRS {{ $item->code }}" style="width:100%;height:650px;border:1px solid #dee2e6;border-radius:8px;" loading="lazy"></iframe>
</div>
</details>
</div><hr><div class="small mb-3">Mata kuliah terkait: <strong>{{ $item->details->where('dosen_id',$user->id)->pluck('mataKuliah.name')->filter()->join(', ') }}</strong></div>
@if($item->status === 'Diajukan')<form method="POST" action="{{ route('dosen.akademik.krs.approve',$item->code) }}" class="d-inline">@csrf<button class="btn btn-success">Setujui KRS</button></form><form method="POST" action="{{ route('dosen.akademik.krs.reject',$item->code) }}" class="d-inline ms-2">@csrf<input name="notes" class="form-control form-control-sm d-inline-block" style="width:220px" required placeholder="Alasan penolakan"><button class="btn btn-danger ms-1">Tolak</button></form>@endif</div></div></div>@empty<div class="col-12"><div class="empty"><p class="empty-title">Tidak ada KRS yang menunggu persetujuan.</p></div></div>@endforelse</div><div class="mt-3">{{ $krs->links() }}</div></div>
@endsection
