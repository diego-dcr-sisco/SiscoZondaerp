<?php

namespace App\Exports;

class DailyTrackingReportExport
{
    /**
     * @var array<int, array<int, mixed>>
     */
    private array $rows;
    /**
     * @var array<int, string>
     */
    private array $headings;

    /**
     * @param array<int, array<int, mixed>> $rows
     * @param array<int, string> $headings
     */
    public function __construct(array $rows, array $headings)
    {
        $this->rows = $rows;
        $this->headings = $headings;
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    public function rows(): array
    {
        return $this->rows;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return $this->headings;
    }
}
