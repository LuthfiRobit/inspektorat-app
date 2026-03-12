<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanKegiatanExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $data;
    protected $filterTexts;

    public function __construct($data, $filterTexts = [])
    {
        $this->data = $data;
        $this->filterTexts = $filterTexts;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        $rows = [
            ['INFORMASI PENARIKAN DATA'],
        ];

        foreach ($this->filterTexts as $key => $value) {
            $rows[] = [$key, ':', $value];
        }

        $rows[] = ['']; // Empty line separator

        // Return all rows, the last one is actual data headers
        $rows[] = [
            'Tahun',
            'Periode',
            'Kecamatan',
            'Desa',
            'Jenis Kegiatan',
            'Nama Kegiatan',
            'Status',
        ];

        return $rows;
    }

    public function map($row): array
    {
        $bulanNama = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];

        return [
            $row['tahun'],
            $bulanNama[$row['bulan']] ?? $row['bulan'],
            $row['nama_kecamatan'],
            $row['nama_desa'],
            $row['jenis_kegiatan'],
            $row['nama_kegiatan'],
            $row['status_display'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // 1 row for title + filters count + 1 empty spacer + 1 header table row
        $headerRow = 1 + count($this->filterTexts) + 1 + 1;

        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
            $headerRow => ['font' => ['bold' => true]],
        ];
    }
}
