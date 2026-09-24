@extends('core-themes.core-backpage')

@section('custom-css')
<style>
    .composer-terminal {
        background:#111827;
        color:#e5e7eb;
        border-radius:12px;
        padding:1rem;
        font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace;
    }
    .composer-output {
        min-height:260px;
        max-height:560px;
        overflow:auto;
        white-space:pre-wrap;
        word-break:break-word;
        background:#030712;
        border:1px solid #374151;
        border-radius:8px;
        padding:1rem;
        color:#d1fae5;
    }
    .composer-prompt { color:#86efac; }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="card-title mb-1"><i class="fas fa-terminal me-2"></i>Terminal Composer</h3>
                    <div class="text-muted small">Maintenance Sistem — Administrator</div>
                </div>
                <a href="{{ route('web-admin.dashboard-render') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Kembali
                </a>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="fas fa-shield-alt me-2"></i>
                    <strong>Khusus Administrator.</strong>
                    Terminal ini hanya menerima perintah Composer dan tidak meneruskan karakter shell seperti <code>;</code>, <code>|</code>, <code>&amp;</code>, <code>$</code>, <code>&gt;</code>, atau <code>&lt;</code>.
                </div>

                @if(session('composer_success'))
                    <div class="alert alert-success">
                        <strong><i class="fas fa-check-circle me-2"></i>{{ session('composer_success') }}</strong>
                    </div>
                @endif

                @if(session('composer_error'))
                    <div class="alert alert-danger">
                        <strong><i class="fas fa-exclamation-triangle me-2"></i>{{ session('composer_error') }}</strong>
                    </div>
                @endif

                <form method="POST" action="{{ route('web-admin.maintenance.composer.run') }}" class="mb-4">
                    @csrf
                    <label class="form-label fw-bold">Perintah Composer</label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-dark text-success font-monospace">composer</span>
                        <input
                            type="text"
                            name="command"
                            class="form-control font-monospace"
                            value="{{ old('command', session('composer_command') ? preg_replace('/^composer\\s*/i', '', session('composer_command')) : 'dump-autoload -o') }}"
                            placeholder="dump-autoload -o"
                            autocomplete="off"
                            required
                        >
                        <button type="submit" class="btn btn-primary" onclick="return confirm('Jalankan perintah Composer ini sekarang?');">
                            <i class="fas fa-play me-1"></i>Eksekusi
                        </button>
                    </div>
                    @error('command')
                        <div class="text-danger small mt-2">{{ $message }}</div>
                    @enderror
                </form>

                <div class="mb-3">
                    <div class="fw-bold mb-2">Perintah cepat</div>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="setComposer('dump-autoload -o')">dump-autoload -o</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="setComposer('install --no-dev --optimize-autoloader --no-interaction')">install --no-dev</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="setComposer('validate')">validate</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="setComposer('show')">show</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="setComposer('diagnose')">diagnose</button>
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="setComposer('clear-cache')">clear-cache</button>
                    </div>
                </div>

                <div class="composer-terminal">
                    <div class="mb-2"><span class="composer-prompt">$ composer {{ session('composer_command') ? e(preg_replace('/^composer\\s*/i', '', session('composer_command'))) : 'dump-autoload -o' }}</span></div>
                    <div class="composer-output">{{ session('composer_output', 'Output Composer akan tampil di sini setelah perintah dijalankan.') }}</div>
                </div>

                <div class="text-muted small mt-3">
                    <i class="fas fa-info-circle me-1"></i>
                    Perintah dijalankan dari root aplikasi Laravel. Composer dapat menggunakan <code>composer.phar</code> di root aplikasi, binary server, atau lokasi yang ditentukan melalui <code>COMPOSER_BINARY</code>.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('custom-js')
<script>
function setComposer(command) {
    const input = document.querySelector('input[name="command"]');
    if (input) {
        input.value = command;
        input.focus();
    }
}
</script>
@endsection
