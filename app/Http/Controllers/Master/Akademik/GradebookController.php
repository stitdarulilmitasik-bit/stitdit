<?php

namespace App\Http\Controllers\Master\Akademik;

use App\Http\Controllers\Controller;
use App\Models\Akademik\KrsDetail;
use App\Models\Akademik\Nilai;
use App\Models\Akademik\TahunAkademik;
use App\Models\Akademik\MataKuliah;
use App\Models\Akademik\Kelas;
use App\Services\Akademik\GradebookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class GradebookController extends Controller
{
    public function __construct(private GradebookService $grades)
    {
    }

    private function isDosen(): bool
    {
        return Auth::guard('dosen')->check();
    }

    private function dosenId(): ?int
    {
        return Auth::guard('dosen')->id();
    }

    private function canTeach(KrsDetail $detail): bool
    {
        if (!$this->isDosen()) {
            return true;
        }

        $dosenId = $this->dosenId();

        return (int) $detail->dosen_id === (int) $dosenId
            || (int) ($detail->mataKuliah->dosen1_id ?? 0) === (int) $dosenId
            || (int) ($detail->mataKuliah->dosen2_id ?? 0) === (int) $dosenId
            || (int) ($detail->mataKuliah->dosen3_id ?? 0) === (int) $dosenId;
    }

    public function index(Request $request)
    {
        $activeTaka = TahunAkademik::where('status', 'Aktif')->first();

        $takaId = (int) $request->input('taka_id', $activeTaka?->id);
        $semester = $request->filled('semester') ? (int) $request->semester : null;
        $matkulId = $request->filled('matkul_id') ? (int) $request->matkul_id : null;
        $kelasId = $request->filled('kelas_id') ? (int) $request->kelas_id : null;

        $detailsQuery = KrsDetail::query()
            ->with([
                'krs.mahasiswa',
                'krs.tahunAkademik',
                'mataKuliah.dosen1',
                'mataKuliah.dosen2',
                'mataKuliah.dosen3',
                'kelas',
                'dosen',
                'nilai',
            ])
            ->where('status', 'Aktif')
            ->whereHas('krs', function ($q) use ($takaId, $semester) {
                $q->whereIn('status', ['approved', 'published', 'locked']);

                if ($takaId) {
                    $q->where('taka_id', $takaId);
                }
                if ($semester) {
                    $q->where('semester', $semester);
                }
            });

        if ($matkulId) {
            $detailsQuery->where('matkul_id', $matkulId);
        }

        if ($kelasId) {
            $detailsQuery->where('kelas_id', $kelasId);
        }

        if ($this->isDosen()) {
            $detailsQuery->where(function ($q) {
                $dosenId = $this->dosenId();
                $q->where('dosen_id', $dosenId)
                    ->orWhereHas('mataKuliah', function ($mk) use ($dosenId) {
                        $mk->where('dosen1_id', $dosenId)
                            ->orWhere('dosen2_id', $dosenId)
                            ->orWhere('dosen3_id', $dosenId);
                    });
            });
        }

        $details = $detailsQuery
            ->orderBy('kelas_id')
            ->orderBy('matkul_id')
            ->get();

        // The gradebook is driven by KRS. A grade row must never exist
        // without a valid enrollment detail.
        foreach ($details as $detail) {
            if (!$detail->nilai) {
                $detail->nilai = Nilai::firstOrCreate(
                    ['krs_detail_id' => $detail->id],
                    [
                        'code' => 'NIL-' . now()->format('Ymd') . '-' . Str::upper(Str::random(8)),
                        'mahasiswa_id' => $detail->krs->mahasiswa_id,
                        'matkul_id' => $detail->matkul_id,
                        'taka_id' => $detail->krs->taka_id,
                        'semester' => $detail->krs->semester,
                        'sks' => $detail->sks,
                        'status' => 'Draft',
                        'bobot_tugas' => 20,
                        'bobot_quiz' => 10,
                        'bobot_uts' => 25,
                        'bobot_uas' => 30,
                        'bobot_praktikum' => 0,
                        'bobot_kehadiran' => 15,
                        'created_by' => Auth::guard('web')->id() ?: Auth::id(),
                    ]
                );
            }
        }

        $takaList = TahunAkademik::orderByDesc('id')->get();
        $matkulList = $this->isDosen()
            ? MataKuliah::where(function ($q) {
                $id = $this->dosenId();
                $q->where('dosen1_id', $id)->orWhere('dosen2_id', $id)->orWhere('dosen3_id', $id);
            })->orderBy('code')->get()
            : MataKuliah::orderBy('code')->get();
        $kelasList = Kelas::when($takaId, fn ($q) => $q->where('taka_id', $takaId))
            ->orderBy('name')
            ->get();

        return view('master.akademik.gradebook-index', [
            'details' => $details,
            'takaList' => $takaList,
            'matkulList' => $matkulList,
            'kelasList' => $kelasList,
            'activeTaka' => $takaId,
            'activeSemester' => $semester,
            'activeMatkul' => $matkulId,
            'activeKelas' => $kelasId,
            'isDosen' => $this->isDosen(),
            'pages' => 'Gradebook / Input Nilai',
            'webs' => \App\Models\Pengaturan\WebSetting::first(),
            'spref' => $this->isDosen() ? 'dosen.' : (Auth::user()->prefix ?? ''),
        ]);
    }

    public function save(Request $request)
    {
        $request->validate([
            'nilai' => 'required|array',
            'nilai.*.id' => 'required|exists:nilais,id',
            'nilai.*.tugas_1' => 'nullable|numeric|min:0|max:100',
            'nilai.*.tugas_2' => 'nullable|numeric|min:0|max:100',
            'nilai.*.tugas_3' => 'nullable|numeric|min:0|max:100',
            'nilai.*.quiz_1' => 'nullable|numeric|min:0|max:100',
            'nilai.*.quiz_2' => 'nullable|numeric|min:0|max:100',
            'nilai.*.uts' => 'nullable|numeric|min:0|max:100',
            'nilai.*.uas' => 'nullable|numeric|min:0|max:100',
            'nilai.*.praktikum' => 'nullable|numeric|min:0|max:100',
            'nilai.*.kehadiran' => 'nullable|numeric|min:0|max:100',
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->input('nilai', []) as $row) {
                $nilai = Nilai::with(['krsDetail.mataKuliah'])->findOrFail($row['id']);

                abort_unless($nilai->status === 'Draft', 422, 'Nilai yang sudah diajukan/disetujui/dipublish tidak dapat diedit.');

                if ($this->isDosen()) {
                    abort_unless($nilai->krsDetail && $this->canTeach($nilai->krsDetail), 403, 'Anda bukan dosen pengampu mata kuliah ini.');
                }

                $before = $nilai->getAttributes();

                foreach (GradebookService::SCORE_FIELDS as $field) {
                    if (array_key_exists($field, $row)) {
                        $nilai->{$field} = $row[$field] === '' ? null : $row[$field];
                    }
                }

                $this->grades->calculate($nilai);
                $nilai->updated_by = Auth::guard('web')->id() ?: Auth::id();
                $nilai->grade_version = ((int) $nilai->grade_version) + 1;
                $nilai->saveQuietly();

                $this->grades->auditScoreChange(
                    $nilai,
                    $before,
                    $nilai->getAttributes(),
                    'Perubahan nilai pada gradebook'
                );
            }
        });

        return back()->with('success', 'Nilai tersimpan. Status tetap Draft sampai dosen mengajukan nilai.');
    }

    public function submit($code)
    {
        $nilai = Nilai::with('krsDetail.mataKuliah')->where('code', $code)->firstOrFail();

        abort_unless($this->isDosen(), 403, 'Pengajuan nilai dilakukan oleh dosen pengampu.');

        abort_unless($nilai->krsDetail && $this->canTeach($nilai->krsDetail), 403, 'Anda bukan dosen pengampu mata kuliah ini.');

        DB::transaction(fn () => $this->grades->transition($nilai, 'Submitted', 'Dosen mengajukan nilai untuk verifikasi akademik.'));

        return back()->with('success', 'Nilai diajukan. Selanjutnya menunggu verifikasi akademik.');
    }

    public function approve($code)
    {
        abort_if($this->isDosen(), 403, 'Dosen tidak dapat menyetujui nilai.');

        $nilai = Nilai::where('code', $code)->firstOrFail();
        DB::transaction(fn () => $this->grades->transition($nilai, 'Approved', 'Nilai diverifikasi oleh unit akademik.'));

        return back()->with('success', 'Nilai disetujui dan siap dipublish.');
    }

    public function publish($code)
    {
        abort_if($this->isDosen(), 403, 'Dosen tidak dapat mempublish nilai.');

        $nilai = Nilai::where('code', $code)->firstOrFail();
        DB::transaction(fn () => $this->grades->transition($nilai, 'Published', 'Nilai dipublikasikan oleh unit akademik.'));

        return back()->with('success', 'Nilai dipublish. Nilai sekarang menjadi sumber resmi KHS.');
    }

    public function lock($code)
    {
        abort_if($this->isDosen(), 403, 'Dosen tidak dapat mengunci nilai.');

        $nilai = Nilai::where('code', $code)->firstOrFail();
        DB::transaction(fn () => $this->grades->transition($nilai, 'Locked', 'Nilai dikunci untuk menjaga integritas data akademik.'));

        return back()->with('success', 'Nilai dikunci.');
    }

    public function reopen(Request $request, $code)
    {
        abort_if($this->isDosen(), 403, 'Pembukaan kembali nilai hanya dapat dilakukan unit akademik.');

        $request->validate(['reason' => 'required|string|min:10|max:1000']);

        $nilai = Nilai::where('code', $code)->firstOrFail();
        DB::transaction(fn () => $this->grades->transition($nilai, 'Approved', $request->reason));

        return back()->with('success', 'Nilai dibuka kembali ke tahap Approved. Alasan perubahan tercatat pada audit trail.');
    }
}
