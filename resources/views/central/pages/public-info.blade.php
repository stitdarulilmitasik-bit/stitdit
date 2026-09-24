@extends('core-themes.core-mainpage')

@section('custom-css')
<style>
    .org-chart { overflow-x: auto; padding: 1rem .25rem 2rem; }
    .org-level { display: flex; justify-content: center; gap: 1.25rem; min-width: max-content; position: relative; }
    .org-level + .org-level { margin-top: 2.75rem; }
    .org-level + .org-level::before { content: ''; position: absolute; top: -1.5rem; left: 50%; height: 1.5rem; border-left: 2px solid var(--tblr-border-color); }
    .org-level::after { content: ''; position: absolute; top: -1.5rem; left: 15%; right: 15%; border-top: 2px solid var(--tblr-border-color); }
    .org-level:first-child::after { display: none; }
    .org-card { width: 260px; border: 1px solid var(--tblr-border-color); border-radius: .75rem; background: var(--tblr-bg-surface); box-shadow: 0 6px 18px rgba(0,0,0,.06); padding: 1rem; text-align: center; position: relative; }
    .org-card::before { content: ''; position: absolute; top: -1.5rem; left: 50%; height: 1.5rem; border-left: 2px solid var(--tblr-border-color); }
    .org-level:first-child .org-card::before { display: none; }
    .org-role { font-size: .78rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--tblr-primary); margin-bottom: .35rem; }
    .org-name { font-weight: 600; line-height: 1.4; }
    .org-top { width: 300px; }
    .org-note { max-width: 900px; margin: 0 auto 1.5rem; }
    .vision-mission { max-width: 980px; margin: 0 auto; }
    .vision-mission-section + .vision-mission-section { margin-top: 2.5rem; }
    .vision-mission-title {
        display: flex;
        align-items: center;
        gap: .75rem;
        min-height: 42px;
        margin: 0 0 1rem;
        padding: .65rem 1rem;
        border-left: 4px solid var(--tblr-primary);
        background: rgba(var(--tblr-primary-rgb), .06);
        border-radius: .35rem;
        font-size: 1.35rem;
        font-weight: 700;
        line-height: 1.25;
    }
    .vision-mission-content {
        margin: 0;
        padding: 0 1rem;
        font-size: 1.05rem;
        line-height: 1.8;
        text-align: justify;
    }
    .vision-mission-list {
        margin: 0;
        padding-left: 2.2rem;
        font-size: 1.05rem;
        line-height: 1.8;
    }
    .vision-mission-list li { padding-left: .35rem; margin-bottom: .7rem; }
    @media (max-width: 767.98px) {
        .org-card { width: 220px; }
        .org-top { width: 240px; }
        .org-level { gap: .75rem; }
        .vision-mission-title { font-size: 1.15rem; }
        .vision-mission-content,
        .vision-mission-list { font-size: 1rem; line-height: 1.7; }
        .vision-mission-content { padding: 0 .25rem; text-align: left; }
        .vision-mission-list { padding-left: 1.75rem; }
    }
</style>
@endsection

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent py-4 text-center">
                    <h1 class="card-title mb-2">{{ $info['heading'] }}</h1>
                    <div class="text-muted">{{ $webs->school_name ?? 'STIT Darul Ilmi Tasikmalaya' }}</div>
                </div>

                <div class="card-body py-4">
                    @if($pages === 'Struktur Organisasi')
                        <div class="org-note text-center text-secondary">
                            Bagan struktur berikut disusun berdasarkan susunan jabatan yang diberikan untuk ditampilkan pada website resmi STIT Darul Ilmi Tasikmalaya.
                        </div>

                        <div class="org-chart">
                            <div class="org-level">
                                @foreach($info['structure']['pembina'] as $name)
                                    <div class="org-card org-top">
                                        <div class="org-role">Pembina</div>
                                        <div class="org-name">{{ $name }}</div>
                                    </div>
                                @endforeach
                                @foreach($info['structure']['ketua'] as $name)
                                    <div class="org-card org-top">
                                        <div class="org-role">Ketua</div>
                                        <div class="org-name">{{ $name }}</div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="org-level">
                                @foreach($info['structure']['wakil'] as $item)
                                    <div class="org-card">
                                        <div class="org-role">{{ $item['label'] }}</div>
                                        <div class="org-name">{{ $item['name'] }}</div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="org-level">
                                @foreach($info['structure']['prodi'] as $item)
                                    <div class="org-card">
                                        <div class="org-role">{{ $item['label'] }}</div>
                                        <div class="org-name">{{ $item['name'] }}</div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="org-level">
                                @foreach($info['structure']['unit'] as $item)
                                    <div class="org-card">
                                        <div class="org-role">{{ $item['label'] }}</div>
                                        <div class="org-name">{{ $item['name'] }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @elseif(isset($info['vision']))
                        <div class="vision-mission">
                            <section class="vision-mission-section">
                                <h2 class="vision-mission-title">Visi</h2>
                                <p class="vision-mission-content">{{ $info['vision'] }}</p>
                            </section>

                            <section class="vision-mission-section">
                                <h2 class="vision-mission-title">Misi</h2>
                                <ol class="vision-mission-list">
                                    @foreach($info['missions'] as $mission)
                                        <li>{{ $mission }}</li>
                                    @endforeach
                                </ol>
                            </section>
                        </div>
                    @else
                        <div class="fs-4 lh-lg">{!! nl2br(e($info['content'] ?? 'Informasi belum tersedia.')) !!}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
