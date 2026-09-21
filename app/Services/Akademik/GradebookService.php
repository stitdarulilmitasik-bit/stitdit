<?php

namespace App\Services\Akademik;

use App\Models\Akademik\Nilai;
use App\Models\Akademik\NilaiAudit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class GradebookService
{
    public const STATUSES = ['Draft', 'Submitted', 'Approved', 'Published', 'Locked'];

    public const SCORE_FIELDS = [
        'tugas_1', 'tugas_2', 'tugas_3',
        'quiz_1', 'quiz_2',
        'uts', 'uas', 'praktikum', 'kehadiran',
    ];

    public const WEIGHT_FIELDS = [
        'bobot_tugas', 'bobot_quiz', 'bobot_uts',
        'bobot_uas', 'bobot_praktikum', 'bobot_kehadiran',
    ];

    public function validateWeights(array $weights): void
    {
        $total = collect(self::WEIGHT_FIELDS)
            ->sum(fn ($field) => (float) ($weights[$field] ?? 0));

        if (abs($total - 100) > 0.01) {
            throw new RuntimeException('Total bobot penilaian harus tepat 100%. Saat ini ' . number_format($total, 2) . '%.');
        }

        foreach (self::WEIGHT_FIELDS as $field) {
            $value = (float) ($weights[$field] ?? 0);
            if ($value < 0 || $value > 100) {
                throw new RuntimeException('Bobot ' . $field . ' harus berada pada rentang 0–100%.');
            }
        }
    }

    public function validateScores(Nilai $nilai): void
    {
        $this->validateWeights($nilai->only(self::WEIGHT_FIELDS));

        $missing = [];
        $pairs = [
            'bobot_tugas' => ['tugas_1', 'tugas_2', 'tugas_3'],
            'bobot_quiz' => ['quiz_1', 'quiz_2'],
            'bobot_uts' => ['uts'],
            'bobot_uas' => ['uas'],
            'bobot_praktikum' => ['praktikum'],
            'bobot_kehadiran' => ['kehadiran'],
        ];

        foreach ($pairs as $weightField => $scoreFields) {
            if ((float) $nilai->{$weightField} <= 0) {
                continue;
            }

            $hasScore = collect($scoreFields)->contains(
                fn ($field) => $nilai->{$field} !== null && $nilai->{$field} !== ''
            );

            if (!$hasScore) {
                $missing[] = str_replace('bobot_', '', $weightField);
            }
        }

        if ($missing) {
            throw new RuntimeException(
                'Nilai belum lengkap. Komponen wajib yang belum memiliki nilai: ' . implode(', ', $missing) . '.'
            );
        }

        foreach (self::SCORE_FIELDS as $field) {
            if ($nilai->{$field} !== null && ((float) $nilai->{$field} < 0 || (float) $nilai->{$field} > 100)) {
                throw new RuntimeException('Nilai ' . $field . ' harus berada pada rentang 0–100.');
            }
        }
    }

    public function calculate(Nilai $nilai): Nilai
    {
        $this->validateWeights($nilai->only(self::WEIGHT_FIELDS));

        $components = [
            'bobot_tugas' => $nilai->rata_tugas,
            'bobot_quiz' => $nilai->rata_quiz,
            'bobot_uts' => $nilai->uts,
            'bobot_uas' => $nilai->uas,
            'bobot_praktikum' => $nilai->praktikum,
            'bobot_kehadiran' => $nilai->kehadiran,
        ];

        $score = 0.0;
        foreach ($components as $weight => $componentScore) {
            if ($componentScore === null) {
                continue;
            }
            $score += ((float) $componentScore * (float) $nilai->{$weight}) / 100;
        }

        $nilai->nilai_angka = round($score, 2);
        $nilai->assignGradeFromScore();
        $nilai->mutu_x_sks = round((float) $nilai->nilai_mutu * (float) $nilai->sks, 2);

        return $nilai;
    }

    public function transition(Nilai $nilai, string $to, ?string $reason = null): Nilai
    {
        $from = $nilai->getRawOriginal('status') ?: $nilai->status;

        $allowed = [
            'Draft' => ['Submitted'],
            'Submitted' => ['Draft', 'Approved'],
            'Approved' => ['Submitted', 'Published'],
            'Published' => ['Locked'],
            'Locked' => ['Approved'],
        ];

        if (!in_array($to, $allowed[$from] ?? [], true)) {
            throw new RuntimeException("Perubahan status {$from} → {$to} tidak diizinkan.");
        }

        if (in_array($to, ['Submitted', 'Approved', 'Published'], true)) {
            $this->calculate($nilai);
            $this->validateScores($nilai);
        }

        $before = $nilai->getAttributes();

        $data = ['status' => $to, 'workflow_note' => $reason];

        if ($to === 'Submitted') {
            $data['submitted_at'] = now();
            $data['submitted_by'] = $this->userId();
        } elseif ($to === 'Approved') {
            $data['approved_at'] = now();
            $data['approved_by'] = $this->userId();
        } elseif ($to === 'Published') {
            $data['published_at'] = now();
        } elseif ($to === 'Locked') {
            $data['locked_at'] = now();
            $data['locked_by'] = $this->userId();
        }

        $data['grade_version'] = ((int) $nilai->grade_version) + 1;
        $nilai->fill($data);
        $nilai->saveQuietly();

        $this->audit($nilai, $from, $to, $reason, $before, $nilai->getAttributes());

        return $nilai->refresh();
    }

    public function audit(Nilai $nilai, string $from, string $to, ?string $reason, array $before, array $after): void
    {
        NilaiAudit::create([
            'nilai_id' => $nilai->id,
            'action' => strtolower($from . '_to_' . $to),
            'from_status' => $from,
            'to_status' => $to,
            'actor_user_id' => Auth::guard('web')->id(),
            'actor_dosen_id' => Auth::guard('dosen')->id(),
            'reason' => $reason,
            'before_data' => $this->snapshot($before),
            'after_data' => $this->snapshot($after),
        ]);
    }

    public function auditScoreChange(Nilai $nilai, array $before, array $after, ?string $reason = null): void
    {
        $this->audit(
            $nilai,
            (string) $nilai->getRawOriginal('status'),
            (string) $nilai->getRawOriginal('status'),
            $reason,
            $before,
            $after
        );
    }

    private function snapshot(array $data): array
    {
        $allowed = array_merge(
            ['id', 'status', 'grade_version', 'nilai_angka', 'nilai_huruf', 'nilai_mutu', 'mutu_x_sks'],
            self::SCORE_FIELDS,
            self::WEIGHT_FIELDS
        );

        return collect($data)->only($allowed)->toArray();
    }

    private function userId(): ?int
    {
        return Auth::guard('web')->id() ?: Auth::id();
    }
}
