<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class DatabaseWorkbookExport implements Export, WithMultipleSheets
{
    /**
     * @param  list<array{title: string, headings: list<string>, rows: list<list<string>>}>  $sheets
     */
    public function __construct(
        protected array $sheets,
    ) {}

    /**
     * @return list<DatabaseSheetExport>
     */
    public function sheets(): array
    {
        if ($this->sheets === []) {
            return [
                new DatabaseSheetExport('Backup', ['Notice'], [['No records were available to export.']]),
            ];
        }

        return array_map(
            fn (array $sheet): DatabaseSheetExport => new DatabaseSheetExport(
                $sheet['title'],
                $sheet['headings'],
                $sheet['rows'],
            ),
            $this->sheets,
        );
    }
}
