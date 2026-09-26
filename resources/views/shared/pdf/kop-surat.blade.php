@php
    $kopLogo = null;
    $kopCandidate = storage_path('app/public/images/logo/logo-vert1.png');
    if (is_file($kopCandidate)) {
        $kopMime = mime_content_type($kopCandidate) ?: 'image/png';
        $kopLogo = 'data:' . $kopMime . ';base64,' . base64_encode(file_get_contents($kopCandidate));
    }
@endphp
<div style="border-bottom:3px solid #111;padding-bottom:6px;margin-bottom:10px;">
    <table style="width:100%;border-collapse:collapse;font-family:DejaVu Sans,Arial,sans-serif;">
        <tr>
            <td style="width:125px;text-align:center;vertical-align:middle;">
                @if($kopLogo)
                    <img src="{{ $kopLogo }}" alt="Logo STIT Darul Ilmi" style="width:110px;height:110px;object-fit:contain;">
                @endif
            </td>
            <td style="text-align:center;vertical-align:middle;line-height:1.22;">
                <div style="font-size:13.5pt;font-weight:bold;">SEKOLAH TINGGI ILMU TARBIYAH</div>
                <div style="font-size:17pt;font-weight:bold;">STIT DARUL ILMI TASIKMALAYA</div>
                <div style="font-size:9pt;font-weight:bold;">SK Menteri Agama RI No. 536 Tahun 2026</div>
                <div style="font-size:8.4pt;">Alamat : Jl. Cirahayu Sindangraja Jamanis Kabupaten Tasikmalaya Jawa Barat 46175</div>
            </td>
        </tr>
    </table>
</div>