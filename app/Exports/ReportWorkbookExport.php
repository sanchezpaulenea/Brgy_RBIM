<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportWorkbookExport implements FromArray, ShouldAutoSize, WithStyles
{
    /**
     * @param  list<string>  $headings
     * @param  list<list<string>>  $rows
     */
    public function __construct(
        protected string $title,
        protected ?string $subtitle,
        protected array $headings,
        protected array $rows,
    ) {}

    /**
     * @return list<list<string>>
     */
    public function array(): array
    {
        $block = [[$this->title]];

        if ($this->subtitle !== null && $this->subtitle !== '') {
            $block[] = [$this->subtitle];
        }

        $block[] = $this->headings;

        return array_merge($block, $this->rows);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:'.$sheet->getHighestColumn().$sheet->getHighestRow())
            ->getAlignment()
            ->setWrapText(true)
            ->setVertical(Alignment::VERTICAL_TOP);

        $headingRow = $this->subtitle !== null && $this->subtitle !== '' ? 3 : 2;

        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            $headingRow => ['font' => ['bold' => true]],
        ];
    }
}
