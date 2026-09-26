@extends('core-themes.core-backpage')

@section('custom-css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
.card{border:none;box-shadow:0 0 10px rgba(0,0,0,.05);border-radius:10px}.card-header{background:none;border-bottom:1px solid rgba(0,0,0,.05);padding:1.5rem}.card-body{padding:1.5rem}.btn{border-radius:5px}.form-control{border-radius:5px;border:1px solid rgba(0,0,0,.1);padding:.5rem 1rem}.form-control:focus{border-color:#435ebe;box-shadow:0 0 0 .2rem rgba(67,94,190,.25)}.badge{padding:.5em .75em;font-weight:500}.gallery-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1rem;margin-top:1rem}.gallery-item{position:relative;border-radius:8px;overflow:hidden;box-shadow:0 2px 4px rgba(0,0,0,.1)}.gallery-item img{width:100%;height:200px;object-fit:cover;transition:transform .3s}.gallery-item:hover img{transform:scale(1.05)}.gallery-item .overlay{position:absolute;bottom:0;left:0;right:0;background:rgba(0,0,0,.7);padding:.5rem;color:#fff;font-size:.875rem}.gallery-item .actions{position:absolute;top:.5rem;right:.5rem;display:none}.gallery-item:hover .actions{display:flex;gap:.5rem}.preview-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:10px;margin-top:10px}.preview-grid img{width:100%;height:120px;object-fit:cover;border-radius:8px}.gallery-empty{border:2px dashed #dee2e6;border-radius:10px;padding:40px 20px;text-align:center;color:#6c757d}
</style>
@endsection

@section('content')
<div class="row">
<div class="col-lg-8 col-12 mb-2">
<div class="card">
<div class="card-header d-flex justify-content-between align-items-center">
<h5 class="mb-0">Detail Galeri - {{ $galeri->name }}</h5>
<button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseForm"><i class="fas fa-plus-circle me-2"></i>Tambah Foto</button>
</div>
<div class="card-body">
<div class="row mb-4"><div class="col-md-12"><div class="d-flex align-items-center mb-3">
<img src="{{ route($spref . 'publikasi.galeri-cover-file', $galeri->code) }}" alt="{{ $galeri->name }}" class="img-fluid rounded" style="max-height:200px">
<div class="ms-3"><h4>{{ $galeri->name }}</h4><p class="text-muted mb-2">Kategori: {{ $galeri->kategori->name }}</p><span class="badge bg-{{ $galeri->status == 'Publish' ? 'success' : ($galeri->status == 'Draft' ? 'warning' : 'secondary') }}">{{ $galeri->status }}</span></div>
</div><h6>Deskripsi:</h6><p>{{ $galeri->content }}</p></div></div>

<div class="collapse" id="collapseForm"><div class="card card-body border mb-4">
<h5 class="card-title mb-2">Tambah Foto Dokumentasi</h5><p class="text-muted">Pilih beberapa foto sekaligus untuk kegiatan ini.</p>
<form action="{{ route($spref . 'publikasi.galeri-foto-handle', $galeri->code) }}" method="post" enctype="multipart/form-data">@csrf
<div class="mb-3"><label for="photos" class="form-label">Foto Dokumentasi</label>
<input type="file" class="form-control" name="photos[]" id="photos" accept="image/jpeg,image/png,image/jpg,image/webp" multiple required onchange="previewMultipleImages(this)">
<small class="text-muted">Bisa memilih banyak foto sekaligus. Maksimal 4 MB per foto.</small>
@error('photos')<small class="text-danger d-block">{{ $message }}</small>@enderror
@error('photos.*')<small class="text-danger d-block">{{ $message }}</small>@enderror
</div>
<div id="preview-container" class="preview-grid mb-3"></div>
<div class="mb-3"><label for="desc" class="form-label">Deskripsi / Keterangan Foto</label><textarea class="form-control" name="desc" id="desc" rows="3" placeholder="Keterangan dokumentasi kegiatan..."></textarea>@error('desc')<small class="text-danger">{{ $message }}</small>@enderror</div>
<div class="d-flex justify-content-end"><button type="submit" class="btn btn-primary"><i class="fas fa-cloud-upload-alt me-2"></i>Upload Semua Foto</button></div>
</form></div></div>

@if($galeri->fotos->count())
<div class="gallery-grid">@foreach($galeri->fotos as $foto)<div class="gallery-item">
<img src="{{ route($spref . 'publikasi.galeri-foto-file', $foto->code) }}" alt="{{ $foto->desc ?: 'Foto dokumentasi '.$galeri->name }}" loading="lazy">
<div class="overlay">{{ $foto->desc ? Str::limit($foto->desc,50) : 'Dokumentasi kegiatan' }}</div>
<div class="actions"><button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete('{{ $foto->code }}')"><i class="fas fa-trash"></i></button></div>
</div>@endforeach</div>
@else
<div class="gallery-empty"><i class="fas fa-images fa-3x mb-3"></i><h5>Belum Ada Foto Dokumentasi</h5><p class="mb-0">Klik <strong>Tambah Foto</strong> untuk mengunggah dokumentasi kegiatan.</p></div>
@endif
</div></div></div>

<div class="col-lg-4 col-12 mb-2"><div class="card"><div class="card-header"><h5 class="card-title">Informasi Galeri</h5></div><div class="card-body">
<p>Kelola foto dokumentasi kegiatan {{ $galeri->name }}.</p>
<div class="alert alert-light-success"><h6>Petunjuk:</h6><ul class="mb-0"><li>Klik Tambah Foto.</li><li>Pilih beberapa foto sekaligus.</li><li>Upload semua foto.</li><li>Hover foto untuk menghapus.</li></ul></div>
<div class="mt-4"><h6>Statistik Galeri</h6><ul class="list-group"><li class="list-group-item d-flex justify-content-between">Total Foto <span class="badge bg-primary rounded-pill">{{ $galeri->fotos->count() }}</span></li><li class="list-group-item d-flex justify-content-between">Dibuat <span class="text-muted">{{ $galeri->created_at->format('d M Y H:i') }}</span></li><li class="list-group-item d-flex justify-content-between">Diperbarui <span class="text-muted">{{ $galeri->updated_at->format('d M Y H:i') }}</span></li></ul></div>
</div></div></div>
</div>

@foreach($galeri->fotos as $foto)<form action="{{ route($spref . 'publikasi.galeri-foto-delete', $foto->code) }}" method="POST" class="d-none" id="delete-form-{{ $foto->code }}">@csrf @method('DELETE')</form>@endforeach
@endsection

@section('custom-js')
<script src="{{ asset('dist') }}/assets/extensions/jquery/jquery.min.js"></script><script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function previewMultipleImages(input){const c=document.getElementById('preview-container');c.innerHTML='';Array.from(input.files||[]).forEach(file=>{if(!file.type.startsWith('image/'))return;const r=new FileReader();r.onload=e=>{const d=document.createElement('div');d.innerHTML=`<img src="${e.target.result}" title="${file.name}" alt="Preview">`;c.appendChild(d)};r.readAsDataURL(file)})}
function confirmDelete(code){Swal.fire({title:'Apakah Anda yakin?',text:'Foto yang dihapus tidak dapat dikembalikan!',icon:'warning',showCancelButton:true,confirmButtonText:'Ya, hapus!',cancelButtonText:'Batal'}).then(r=>{if(r.isConfirmed)document.getElementById('delete-form-'+code).submit()})}
</script>
@endsection