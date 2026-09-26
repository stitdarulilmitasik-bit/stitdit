@extends('core-themes.core-mainpage')

@section('custom-css')
<style>
    .pmb-hero {
        background: linear-gradient(135deg, rgba(var(--tblr-primary-rgb), .12), rgba(var(--tblr-info-rgb), .08));
        border: 1px solid var(--tblr-border-color-light);
        border-radius: 1.5rem;
        padding: 2rem;
        margin-bottom: 1.5rem;
    }
    .pmb-card {
        border: 1px solid var(--tblr-border-color-light);
        border-radius: 1.25rem;
        background: var(--tblr-card-bg);
        box-shadow: 0 8px 30px rgba(0,0,0,.06);
    }
    .pmb-card .card-header {
        background: transparent;
        border-bottom: 1px solid var(--tblr-border-color-light);
        padding: 1.25rem 1.5rem;
    }
    .pmb-card .card-body { padding: 1.5rem; }
    .section-title {
        font-weight: 700;
        color: var(--tblr-primary);
        border-left: 4px solid var(--tblr-primary);
        padding-left: .75rem;
        margin: 1.5rem 0 1rem;
    }
    .required::after { content: ' *'; color: var(--tblr-danger); }
    .pmb-number {
        font-size: 1.35rem;
        font-weight: 800;
        letter-spacing: .04em;
        color: var(--tblr-primary);
    }
    @media (max-width: 767.98px) {
        .pmb-hero { padding: 1.25rem; border-radius: 1rem; }
        .pmb-card .card-body { padding: 1rem; }
    }
</style>
@endsection

@section('content')
<div class="container-xl py-4">
    <div class="pmb-hero">
        <div class="row align-items-center g-3">
            <div class="col-lg-8">
                <span class="badge bg-primary-lt mb-2">PMB {{ now()->format('Y') }}</span>
                <h1 class="mb-2">Pendaftaran Calon Mahasiswa Baru</h1>
                <p class="text-secondary mb-0">
                    Lengkapi data berikut untuk mengajukan pendaftaran sebagai calon mahasiswa
                    {{ $webs->school_name ?? 'STIT Darul Ilmi' }}.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="{{ route('root.prodi-index') }}" class="btn btn-outline-primary">
                    Lihat Program Studi
                </a>
            </div>
        </div>
    </div>

    @if(session('registration_success'))
        @php($success = session('registration_success'))
        <div class="alert alert-success">
            <h4 class="alert-title">Pendaftaran berhasil dikirim.</h4>
            <div class="text-secondary mb-2">Simpan nomor registrasi berikut untuk keperluan pengecekan status.</div>
            <div class="pmb-number mb-2">{{ $success['numb_reg'] }}</div>
            <div>Nama: <strong>{{ $success['name'] }}</strong></div>
            <div>Program Studi: <strong>{{ $success['prodi'] }}</strong></div>
            <div>Kode Pendaftaran: <strong>{{ $success['code'] }}</strong></div>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($gelombangs->isEmpty())
        <div class="alert alert-warning">
            <h4 class="alert-title">Pendaftaran sedang tidak tersedia.</h4>
            <div class="text-secondary">
                Belum ada gelombang pendaftaran yang aktif pada tanggal hari ini.
                Silakan hubungi panitia Penerimaan Mahasiswa Baru.
            </div>
        </div>
    @else
        <form action="{{ route('root.pendaftaran-mahasiswa-baru-store') }}" method="POST" enctype="multipart/form-data" class="pmb-card">
            @csrf
            <div class="card-header">
                <h2 class="card-title mb-0">Formulir Pendaftaran</h2>
            </div>
            <div class="card-body">
                <div class="text-secondary small mb-3">
                    Kolom bertanda <span class="text-danger fw-bold">*</span> wajib diisi.
                </div>
                @if($errors->any())
                    <div class="alert alert-danger">
                        <div class="fw-bold mb-1">Periksa kembali data pendaftaran:</div>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <h3 class="section-title">Data Calon Mahasiswa</h3>
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="form-label required">Nama Calon Mahasiswa</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label required">NIK</label>
                        <input type="text" name="numb_ktp" class="form-control" value="{{ old('numb_ktp') }}" inputmode="numeric" maxlength="30" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label required">Nomor WhatsApp</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="08xxxxxxxxxx" inputmode="tel" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label required">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label required">Jenis Kelamin</label>
                        <select name="bio_gender" class="form-select" required>
                            <option value="">Pilih</option>
                            <option value="Laki-laki" @selected(old('bio_gender') === 'Laki-laki')>Laki-laki</option>
                            <option value="Perempuan" @selected(old('bio_gender') === 'Perempuan')>Perempuan</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label required">Tempat Lahir</label>
                        <input type="text" name="bio_placebirth" class="form-control" value="{{ old('bio_placebirth') }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label required">Tanggal Lahir</label>
                        <input type="date" name="bio_datebirth" class="form-control" value="{{ old('bio_datebirth') }}" required>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label required">Agama</label>
                        <select name="bio_religion" class="form-select" required>
                            <option value="">Pilih Agama</option>
                            @foreach(['Islam','Kristen','Katolik','Hindu','Buddha','Konghucu'] as $agama)
                                <option value="{{ $agama }}" @selected(old('bio_religion') === $agama)>{{ $agama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <h3 class="section-title">Alamat Calon Mahasiswa</h3>
                <div class="row">
                    <div class="col-12 mb-3">
                        <label class="form-label required">Alamat</label>
                        <textarea name="ktp_addres" class="form-control" rows="3" required>{{ old('ktp_addres') }}</textarea>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label required">RT</label>
                        <input type="text" name="ktp_rt" class="form-control" value="{{ old('ktp_rt') }}" maxlength="10" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label required">RW</label>
                        <input type="text" name="ktp_rw" class="form-control" value="{{ old('ktp_rw') }}" maxlength="10" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label required">Desa/Kelurahan</label>
                        <input type="text" name="ktp_village" class="form-control" value="{{ old('ktp_village') }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label required">Kecamatan</label>
                        <input type="text" name="ktp_subdistrict" class="form-control" value="{{ old('ktp_subdistrict') }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label required">Kabupaten/Kota</label>
                        <input type="text" name="ktp_city" class="form-control" value="{{ old('ktp_city') }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label required">Provinsi</label>
                        <input type="text" name="ktp_province" class="form-control" value="{{ old('ktp_province') }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label required">Kode Pos</label>
                        <input type="text" name="ktp_poscode" class="form-control" value="{{ old('ktp_poscode') }}" inputmode="numeric" maxlength="10" required>
                    </div>
                </div>

                <h3 class="section-title">Pilihan Pendidikan</h3>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label required">Fakultas Tujuan</label>
                        <select name="fakultas_id" id="fakultas_id" class="form-select" required>
                            <option value="">Pilih Fakultas</option>
                            @foreach($fakultas as $item)
                                <option value="{{ $item->id }}" @selected(old('fakultas_id') == $item->id)>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label required">Program Studi Tujuan</label>
                        <select name="prodi_id" id="prodi_id" class="form-select" required disabled>
                            <option value="">Pilih Fakultas terlebih dahulu</option>
                        </select>
                        @error('prodi_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>

                <h3 class="section-title">Dokumen Persyaratan PMB</h3>
                <div class="alert alert-info">
                    <div class="fw-bold mb-1">Unggah dokumen sesuai persyaratan jalur pendaftaran.</div>
                    <div class="small">Format: PDF, JPG, JPEG, PNG. Maksimal 5 MB per dokumen.</div>
                </div>
                <div id="dokumen-empty" class="text-secondary small mb-3">
                    Pilih Jalur Pendaftaran terlebih dahulu untuk menampilkan persyaratan dokumen.
                </div>
                <div id="dokumen-list"></div>

                <h3 class="section-title">Data Pendaftaran</h3>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label required">Jalur Pendaftaran</label>
                        <select name="jalur_id" id="jalur_id" class="form-select" required>
                            <option value="">Pilih Jalur</option>
                            @foreach($jalurs as $jalur)
                                <option value="{{ $jalur->id }}" @selected(old('jalur_id') == $jalur->id)>{{ $jalur->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label required">Gelombang Pendaftaran</label>
                        <select name="gelombang_id" id="gelombang_id" class="form-select" required>
                            <option value="">Pilih Gelombang</option>
                            @foreach($gelombangs as $gelombang)
                                <option value="{{ $gelombang->id }}"
                                    data-jalur="{{ $gelombang->jalur_id }}"
                                    @selected(old('gelombang_id') == $gelombang->id)>
                                    {{ $gelombang->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label required">Jenis Kelas</label>
                        <select name="jenis_id" class="form-select" required>
                            <option value="">Pilih Jenis Kelas</option>
                            @foreach($jenisKelas as $jenis)
                                <option value="{{ $jenis->id }}" @selected(old('jenis_id') == $jenis->id)>{{ $jenis->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="border-top pt-4 mt-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div class="text-secondary small">
                        Dengan mengirim formulir, calon mahasiswa menyatakan data yang diberikan benar.
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg px-4">
                        Kirim Pendaftaran
                    </button>
                </div>
            </div>
        </form>
    @endif
</div>
@endsection

@section('custom-js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const fakultas = @json($fakultas);
    const fakultasSelect = document.getElementById('fakultas_id');
    const prodiSelect = document.getElementById('prodi_id');
    const gelombangSelect = document.getElementById('gelombang_id');
    const jalurSelect = document.getElementById('jalur_id');
    const dokumenList = document.getElementById('dokumen-list');
    const dokumenEmpty = document.getElementById('dokumen-empty');
    const oldProdi = @json(old('prodi_id'));
    const syaratByJalur = @json($syaratByJalur);

    function loadProdi() {
        const selected = fakultas.find(item => String(item.id) === String(fakultasSelect.value));
        prodiSelect.innerHTML = '<option value="">Pilih Program Studi</option>';

        if (!selected || !selected.program_studis || !selected.program_studis.length) {
            prodiSelect.disabled = true;
            return;
        }

        selected.program_studis.forEach(prodi => {
            const option = document.createElement('option');
            option.value = prodi.id;
            option.textContent = prodi.name;
            if (String(prodi.id) === String(oldProdi)) option.selected = true;
            prodiSelect.appendChild(option);
        });

        prodiSelect.disabled = false;
    }

    function loadDokumen() {
        const jalur = jalurSelect.value;
        dokumenList.innerHTML = '';
        const syarats = syaratByJalur[jalur] || [];

        if (!jalur) {
            dokumenEmpty.textContent = 'Pilih Jalur Pendaftaran terlebih dahulu untuk menampilkan persyaratan dokumen.';
            dokumenEmpty.classList.remove('d-none');
            return;
        }

        if (!syarats.length) {
            dokumenEmpty.textContent = 'Tidak ada dokumen persyaratan yang terdaftar untuk jalur ini.';
            dokumenEmpty.classList.remove('d-none');
            return;
        }

        dokumenEmpty.classList.add('d-none');

        syarats.forEach(function (syarat) {
            const wrapper = document.createElement('div');
            wrapper.className = 'mb-3';
            const label = document.createElement('label');
            label.className = 'form-label required';
            label.textContent = syarat.name;
            const input = document.createElement('input');
            input.type = 'file';
            input.name = 'dokumen[' + syarat.id + ']';
            input.className = 'form-control';
            input.accept = '.pdf,.jpg,.jpeg,.png';
            input.required = true;
            wrapper.appendChild(label);
            wrapper.appendChild(input);

            if (syarat.desc) {
                const help = document.createElement('div');
                help.className = 'form-text';
                help.textContent = syarat.desc;
                wrapper.appendChild(help);
            }

            dokumenList.appendChild(wrapper);
        });
    }

    function filterGelombang() {
        const jalur = jalurSelect.value;
        Array.from(gelombangSelect.options).forEach(option => {
            if (!option.value) return;
            option.hidden = jalur && option.dataset.jalur !== jalur;
        });

        const selected = gelombangSelect.options[gelombangSelect.selectedIndex];
        if (selected && selected.hidden) {
            gelombangSelect.value = '';
        }
    }

    fakultasSelect.addEventListener('change', loadProdi);
    jalurSelect.addEventListener('change', filterGelombang);
    jalurSelect.addEventListener('change', loadDokumen);

    loadProdi();
    filterGelombang();
    loadDokumen();
});
</script>
@endsection
