<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CollectionsReport implements WithMultipleSheets
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function sheets(): array
    {
        return [
            new CollectionsReportSummary($this->startDate, $this->endDate),
            new CollectionsReportDetailed($this->startDate, $this->endDate),
        ];
    }
    
}
