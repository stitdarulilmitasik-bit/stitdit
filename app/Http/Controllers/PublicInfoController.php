<?php

namespace App\Http\Controllers;

use App\Models\Pengaturan\WebSetting;

class PublicInfoController extends Controller
{
    public function show(string $page = 'profil')
    {
        $webs = WebSetting::first();
        $pages = [
            'profil' => [
                'title' => 'Profil Kampus',
                'heading' => 'Profil Kampus',
                'content' => $webs?->school_desc ?: 'Informasi profil kampus belum diisi.',
            ],
            'visi-misi' => [
                'title' => 'Visi & Misi',
                'heading' => 'Visi & Misi',
                'vision' => 'Terwujudnya STIT Darul Ilmi Tasikmalaya yang unggul dalam menghasilkan tenaga profesional yang berkarakter Islami serta mampu mengimplementasikan nilai-nilai Islam dalam kehidupan masyarakat.',
                'missions' => [
                    'Menyelenggarakan kegiatan pembelajaran yang unggul dan berkualitas dalam bidang Ilmu Tarbiyah.',
                    'Mengembangkan Ilmu Tarbiyah melalui pengkajian dan penelitian yang bermanfaat bagi pengembangan dan pemberdayaan masyarakat.',
                    'Memberikan kontribusi terhadap peningkatan kualitas karakter bangsa.',
                ],
            ],
            'struktur-organisasi' => [
                'title' => 'Struktur Organisasi',
                'heading' => 'Bagan Struktur Organisasi STIT Darul Ilmi Tasikmalaya',
                'structure' => [
                    'pembina' => ['Prof. Dr. H. Ah. Fathonih, M.Ag.'],
                    'ketua' => ['Dr. H. Dudung Rahmat Hidayat, M.Pd.'],
                    'wakil' => [
                        ['label' => 'Pembantu Ketua I', 'name' => 'Dr. Asep Rusmana, M.Ag.'],
                        ['label' => 'Pembantu Ketua II', 'name' => 'Aa Sudirman, M.Pd.I.'],
                        ['label' => 'Pembantu Ketua III', 'name' => 'Drs. Emin Salimin, M.A.'],
                    ],
                    'prodi' => [
                        ['label' => 'Ketua Prodi MPI', 'name' => 'H. Angga Yogaswara, Lc, M.Pd.I.'],
                        ['label' => 'Sekretaris Prodi MPI', 'name' => 'Aceng Kosim, M.Pd.'],
                    ],
                    'unit' => [
                        ['label' => 'BAAK', 'name' => 'Drs. Nasihudin, M.Pd.I.'],
                        ['label' => 'BAUK', 'name' => 'Wina Munawaroh, S.Ak.'],
                        ['label' => 'Kepala Perpustakaan', 'name' => 'Dedeh Holipah, M.Pd.'],
                    ],
                ],
            ],
            'fasilitas' => [
                'title' => 'Fasilitas',
                'heading' => 'Fasilitas Kampus',
                'content' => 'Informasi fasilitas kampus dapat diperbarui melalui pengaturan website.',
            ],
            'kontak' => [
                'title' => 'Kontak',
                'heading' => 'Kontak Kampus',
                'content' => $webs?->school_address ?: 'Alamat kampus belum diisi.',
            ],
            'jadwal-kuliah' => ['title'=>'Jadwal Kuliah', 'heading'=>'Jadwal Kuliah', 'content'=>'Jadwal kuliah tersedia melalui portal SIAKAD mahasiswa.'],
            'silabus' => ['title'=>'Silabus', 'heading'=>'Silabus', 'content'=>'Silabus mata kuliah tersedia melalui portal akademik.'],
            'e-learning' => ['title'=>'E-Learning', 'heading'=>'E-Learning', 'content'=>'Layanan E-Learning akan tersedia melalui portal pembelajaran.'],
            'organisasi' => ['title'=>'Organisasi Mahasiswa', 'heading'=>'Organisasi Mahasiswa', 'content'=>'Informasi organisasi mahasiswa akan ditampilkan di halaman ini.'],
            'beasiswa' => ['title'=>'Beasiswa', 'heading'=>'Beasiswa', 'content'=>'Informasi beasiswa akan ditampilkan di halaman ini.'],
            'prestasi' => ['title'=>'Prestasi Mahasiswa', 'heading'=>'Prestasi Mahasiswa', 'content'=>'Informasi prestasi mahasiswa akan ditampilkan di halaman ini.'],
            'alumni' => ['title'=>'Alumni', 'heading'=>'Alumni', 'content'=>'Informasi alumni akan ditampilkan di halaman ini.'],
            'perpustakaan' => ['title'=>'Perpustakaan', 'heading'=>'Perpustakaan', 'content'=>'Layanan perpustakaan kampus.'],
            'laboratorium' => ['title'=>'Laboratorium', 'heading'=>'Laboratorium', 'content'=>'Informasi laboratorium kampus.'],
            'kemahasiswaan' => ['title'=>'Kemahasiswaan', 'heading'=>'Kemahasiswaan', 'content'=>'Layanan kemahasiswaan kampus.'],
            'keuangan' => ['title'=>'Keuangan', 'heading'=>'Keuangan', 'content'=>'Informasi layanan keuangan kampus.'],
        ];

        abort_unless(isset($pages[$page]), 404);

        return view('central.pages.public-info', [
            'webs' => $webs,
            'menus' => 'Institusi',
            'pages' => $pages[$page]['title'],
            'academy' => $webs ? $webs->school_apps . ' by ' . $webs->school_name : 'SIAKAD',
            'info' => $pages[$page],
            'user' => auth()->user() ?: auth()->guard('dosen')->user() ?: auth()->guard('mahasiswa')->user(),
            'spref' => 'web-admin.',
        ]);
    }
}
