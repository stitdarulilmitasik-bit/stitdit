<?php

namespace App\Exports;

use App\Models\Mahasiswa;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class DosenMahasiswaExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(
        private $semester = null,
        private $kelasId = null,
        private $prodiId = null,
        private string $search = ''
    ) {}

    public function query()
    {
        return Mahasiswa::query()
            ->with(['programStudi', 'kelas'])
            ->when($this->semester !== null && $this->semester !== '', fn ($q) => $q->where('semester', (int) $this->semester))
            ->when($this->kelasId, fn ($q) => $q->where('kelas_id', $this->kelasId))
            ->when($this->prodiId, fn ($q) => $q->where('prodi_id', $this->prodiId))
            ->when($this->search !== '', function ($q) {
                $q->where(function ($sub) {
                    $sub->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('numb_nim', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('name');
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Mahasiswa',
            'NIM',
            'Nomor Telepon',
            'Tanggal Lahir',
            'Alamat',
            'Program Studi',
            'Kelas',
            'Semester',
            'Status',
        ];
    }

    public function map($mahasiswa): array
    {
        $attributes = $mahasiswa->getAttributes();

        return [
            $mahasiswa->id,
            $mahasiswa->name,
            $mahasiswa->numb_nim ?? null,
            $attributes['phone'] ?? null,
            $attributes['bio_datebirth'] ?? null,
            $attributes['ktp_addres'] ?? null,
            optional($mahasiswa->programStudi)->name,
            optional($mahasiswa->kelas)->name,
            $mahasiswa->semester,
            $mahasiswa->type,
        ];
    }
}
