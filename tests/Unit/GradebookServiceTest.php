<?php

namespace Tests\Unit;

use App\Models\Akademik\Nilai;
use App\Services\Akademik\GradebookService;
use RuntimeException;
use Tests\TestCase;

class GradebookServiceTest extends TestCase
{
    public function test_configured_weights_are_used_without_hidden_normalization(): void
    {
        $nilai = new Nilai([
            'bobot_tugas' => 20,
            'bobot_quiz' => 10,
            'bobot_uts' => 25,
            'bobot_uas' => 30,
            'bobot_praktikum' => 0,
            'bobot_kehadiran' => 15,
            'tugas_1' => 80,
            'quiz_1' => 70,
            'uts' => 75,
            'uas' => 90,
            'kehadiran' => 100,
            'sks' => 3,
        ]);

        $service = app(GradebookService::class);
        $service->calculate($nilai);

        $this->assertSame(83.75, (float) $nilai->nilai_angka);
        $this->assertSame('A-', $nilai->nilai_huruf);
        $this->assertSame(3.67, (float) $nilai->nilai_mutu);
        $this->assertSame(11.01, (float) $nilai->mutu_x_sks);
    }

    public function test_weights_must_total_one_hundred(): void
    {
        $this->expectException(RuntimeException::class);

        app(GradebookService::class)->validateWeights([
            'bobot_tugas' => 20,
            'bobot_quiz' => 10,
            'bobot_uts' => 25,
            'bobot_uas' => 30,
            'bobot_praktikum' => 0,
            'bobot_kehadiran' => 10,
        ]);
    }
}
