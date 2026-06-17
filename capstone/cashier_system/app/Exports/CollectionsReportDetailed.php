<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class CollectionsReportDetailed implements FromArray, WithEvents, WithTitle, WithColumnWidths
{
    protected $startDate;
    protected $endDate;
    protected $month;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = Carbon::parse($startDate)->startOfDay();
        $this->endDate = Carbon::parse($endDate)->endOfDay();

        $this->month = Carbon::parse($startDate);
    }

    public function array(): array
    {
        return [];
    }

    public function title(): string
    {
        return $this->month->format('FY');
    }

    public function columnWidths(): array
    {
        return [
            'A' => 9,
            'B' => 17,
            'C' => 18,
            'D' => 13,
            'E' => 12,
            'F' => 12,
            'G' => 12,
            'H' => 9,
            'I' => 8,
            'J' => 7,
            'K' => 6,
            'L' => 7,
            'M' => 7,
            'N' => 8,
            'O' => 8,
            'P' => 9,
            'Q' => 7,
            'R' => 7,
            'S' => 10,
            'T' => 10,
            'U' => 2,
            'V' => 11,
            'W' => 12,
            'X' => 11,
            'Y' => 11,
            'Z' => 11,
            'AA' => 11,
            'AB' => 13,
            'AC' => 12,
            'AD' => 12,
            'AE' => 11,
            'AF' => 10,
            'AG' => 13,
            'AH' => 11,
            'AI' => 15,
            'AJ' => 11,
            'AK' => 10,
            'AL' => 12,
            'AM' => 3,
            'AN' => 7,
            'AO' => 9,
            'AP' => 6,
            'AQ' => 8,
            'AR' => 13,
            'AS' => 12,
            'AT' => 12,
            'AU' => 11,
            'AV' => 12,
            'AW' => 12,
            'AX' => 8,
            'AY' => 12,
            'AZ' => 14,
            'BA' => 10,
            'BB' => 9,
            'BC' => 7,
            'BD' => 7,
            'BE' => 7,
            'BF' => 9,
            'BG' => 9,
            'BH' => 9,
            'BI' => 9,
            'BJ' => 0,
            'BK' => 10,
            'BL' => 13,
            'BM' => 13,
            'BN' => 11,
            'BO' => 12,
            'BP' => 13,
            'BQ' => 8,
            'BR' => 11,
            'BS' => 10,
            'BT' => 9,
            'BU' => 10,
            'BV' => 12,
            'BW' => 8,
            'BX' => 8,
            'BY' => 9,
            'BZ' => 9,
            'CA' => 8,
            'CB' => 7,
            'CC' => 7,
            'CD' => 9
        ];
    }

    public function registerEvents(): array
    {
        return [

            AfterSheet::class => function ($event) {

                $sheet = $event->sheet->getDelegate();

                    //Hard Coded formatting below:

                    //Hard Coded Values:

                    $event->sheet->getDelegate()->getRowDimension('1')->setRowHeight(32);
                    $event->sheet->getDelegate()->getRowDimension('2')->setRowHeight(24);
                    $event->sheet->getDelegate()->getRowDimension('3')->setRowHeight(27);
                    $event->sheet->getDelegate()->getRowDimension('4')->setRowHeight(23);
                    $event->sheet->getDelegate()->getRowDimension('5')->setRowHeight(25);

                    $sheet->setCellValue('B1', 'REPORT OF COLLECTIONS');
                    $sheet->setCellValue('J3', strtoupper($this->startDate->format('F j Y')) . ' TO ' . strtoupper($this->endDate->format('F j Y')));
                    $sheet->setCellValue('B5', 'PUP TAGUIG CAMPUS');

                    $sheet->setCellValue('A7', 'DATE');
                    $sheet->setCellValue('B8', 'OFFICIAL');
                    $sheet->setCellValue('B9', 'RECEIPT');
                    $sheet->setCellValue('B10', 'NUMBER');
                    $sheet->setCellValue('C8', 'TOTAL');
                    $sheet->setCellValue('C10', 'COLLECTION');
                    $sheet->setCellValue('D8', 'Certification');
                    $sheet->setCellValue('D10', 'Fee');
                    $sheet->setCellValue('D12', "()");
                    $sheet->setCellValue('E8', 'Certification');
                    $sheet->setCellValue('E10', 'of Grades');
                    $sheet->setCellValue('E12', "()");
                    $sheet->setCellValue('F8', 'Certification');
                    $sheet->setCellValue('F10', 'of Documents');
                    $sheet->setCellValue('F12', "()");
                    $sheet->setCellValue('G8', 'Fines & ');
                    $sheet->setCellValue('G9', 'Penalties');
                    $sheet->setCellValue('G10', 'Service Income');
                    $sheet->setCellValue('G12', "()");
                    $sheet->setCellValue('H7', 'OTHER SERVICE INCOME');
                    $sheet->setCellValue('H8', 'ID');
                    $sheet->setCellValue('H10', '');
                    $sheet->setCellValue('I8', 'Entrance');
                    $sheet->setCellValue('I9', 'Exam');
                    $sheet->setCellValue('I10', '');
                    $sheet->setCellValue('J8', 'English Plus');
                    $sheet->setCellValue('J9', 'Test');
                    $sheet->setCellValue('J10', '');
                    $sheet->setCellValue('K8', 'Module');
                    $sheet->setCellValue('K10', '');
                    $sheet->setCellValue('L8', 'NSTP');
                    $sheet->setCellValue('L9', 'Module');
                    $sheet->setCellValue('L10', '');
                    $sheet->setCellValue('M8', 'Graduation');
                    $sheet->setCellValue('M9', 'Forum');
                    $sheet->setCellValue('M10', '');
                    $sheet->setCellValue('N8', 'Publication');
                    $sheet->setCellValue('N10', '');
                    $sheet->setCellValue('O8', 'Income from');
                    $sheet->setCellValue('O9', 'IT');
                    $sheet->setCellValue('O10', '');
                    $sheet->setCellValue('P8', 'Publication');
                    $sheet->setCellValue('P9', 'Fees');
                    $sheet->setCellValue('P10', '');
                    $sheet->setCellValue('Q8', 'Permit');
                    $sheet->setCellValue('Q9', 'Fee');
                    $sheet->setCellValue('Q10', '');
                    $sheet->setCellValue('R8', 'Duplicate');
                    $sheet->setCellValue('R10', '');
                    $sheet->setCellValue('S8', 'HD');
                    $sheet->setCellValue('S10', '');
                    $sheet->setCellValue('T8', 'Completion');
                    $sheet->setCellValue('T10', '');
                    $sheet->setCellValue('H12', "()");

                    $sheet->setCellValue('V7', 'DATE');
                    $sheet->setCellValue('W7', 'OTHER SERVICE INCOME');
                    $sheet->setCellValue('W8', 'Readmission');
                    $sheet->setCellValue('W10', '');
                    $sheet->setCellValue('X8', 'Retrieval');
                    $sheet->setCellValue('X10', '');
                    $sheet->setCellValue('Y8', 'Handbook');
                    $sheet->setCellValue('Y10', '');
                    $sheet->setCellValue('Z8', 'Scannable');
                    $sheet->setCellValue('Z10', '');
                    $sheet->setCellValue('AA8', 'Change Subject/');
                    $sheet->setCellValue('AA9', 'Curriculum/Shifting');
                    $sheet->setCellValue('AA10', '');
                    $sheet->setCellValue('AB8', 'Accreditation');
                    $sheet->setCellValue('AB10', '');
                    $sheet->setCellValue('AC8', 'Evaluation');
                    $sheet->setCellValue('AC10', '');
                    $sheet->setCellValue('AD8', 'Guidance');
                    $sheet->setCellValue('AD9', 'Fee');
                    $sheet->setCellValue('AD10', '');
                    $sheet->setCellValue('AE8', 'Psychological');
                    $sheet->setCellValue('AE9', 'Exam');
                    $sheet->setCellValue('AE10', '');
                    $sheet->setCellValue('AF8', 'Developmental');
                    $sheet->setCellValue('AF9', 'Fees');
                    $sheet->setCellValue('AF10', '');
                    $sheet->setCellValue('AG8', 'SIS');
                    $sheet->setCellValue('AG10', '');
                    $sheet->setCellValue('AH8', 'ITD');
                    $sheet->setCellValue('AH10', '');
                    $sheet->setCellValue('AI8', 'Authentication');
                    $sheet->setCellValue('AI9', 'Fee');
                    $sheet->setCellValue('AI10', '');
                    $sheet->setCellValue('AJ8', 'Sports');
                    $sheet->setCellValue('AJ9', 'Development');
                    $sheet->setCellValue('AJ10', '');
                    $sheet->setCellValue('AK8', 'Deposit');
                    $sheet->setCellValue('AK10', '');
                    $sheet->setCellValue('AL8', 'Energy');
                    $sheet->setCellValue('AL9', 'Fee');
                    $sheet->setCellValue('AL10', '');
                    $sheet->setCellValue('W12', '()');

                    $sheet->setCellValue('AO7', 'DATE');
                    $sheet->setCellValue('AP7', 'OTHER SERVICE INCOME');
                    $sheet->setCellValue('AP8', 'Tutorial');
                    $sheet->setCellValue('AP10', '');
                    $sheet->setCellValue('AQ8', 'Memorabilia');
                    $sheet->setCellValue('AQ10', '');
                    $sheet->setCellValue('AR8', 'Verification');
                    $sheet->setCellValue('AR10', '');
                    $sheet->setCellValue('AP12', '()');
                    $sheet->setCellValue('AS8', 'Documentary');
                    $sheet->setCellValue('AS10', 'Stamp');
                    $sheet->setCellValue('AS12', '()');
                    $sheet->setCellValue('AT8', 'Insurance');
                    $sheet->setCellValue('AT12', '()');
                    $sheet->setCellValue('AU8', 'Participation');
                    $sheet->setCellValue('AU9', 'in');
                    $sheet->setCellValue('AU10', 'Sports');
                    $sheet->setCellValue('AU11', 'Competition');
                    $sheet->setCellValue('AU12', '()');
                    $sheet->setCellValue('AV8', 'Sports');
                    $sheet->setCellValue('AV9', 'Related');
                    $sheet->setCellValue('AV10', 'Training');
                    $sheet->setCellValue('AV11', 'Fee');
                    $sheet->setCellValue('AV12', '');
                    $sheet->setCellValue('AV8', 'Sports');
                    $sheet->setCellValue('AV9', 'Participation');
                    $sheet->setCellValue('AV10', 'Fee');
                    $sheet->setCellValue('AV12', '');
                    $sheet->setCellValue('AX9', 'Catalyst');
                    $sheet->setCellValue('AX10', 'Fee');
                    $sheet->setCellValue('AY8', 'Student');
                    $sheet->setCellValue('AY9', 'Council');
                    $sheet->setCellValue('AY10', 'Fee');
                    $sheet->setCellValue('AY12', '()');
                    $sheet->setCellValue('AZ8', 'Tuition Fees');
                    $sheet->setCellValue('AZ12', '()');
                    $sheet->setCellValue('BA8', 'Tuition');
                    $sheet->setCellValue('BA9', 'Fees');
                    $sheet->setCellValue('BA11', 'NSTP');
                    $sheet->setCellValue('BA12', '()');
                    $sheet->setCellValue('BB7', 'SCHOOL FEES');
                    $sheet->setCellValue('BB8', 'Cultural');
                    $sheet->setCellValue('BB10', '');
                    $sheet->setCellValue('BC8', 'Athletic');
                    $sheet->setCellValue('BC9', 'Fees');
                    $sheet->setCellValue('BC10', '');
                    $sheet->setCellValue('BD8', 'Athletic');
                    $sheet->setCellValue('BD9', 'Development');
                    $sheet->setCellValue('BD10', '');
                    $sheet->setCellValue('BE8', 'Diploma');
                    $sheet->setCellValue('BE10', '');
                    $sheet->setCellValue('BF8', 'Graduation');
                    $sheet->setCellValue('BF9', 'Fee');
                    $sheet->setCellValue('BF10', '');
                    $sheet->setCellValue('BG8', 'Library');
                    $sheet->setCellValue('BG9', 'Fee');
                    $sheet->setCellValue('BG10', '');
                    $sheet->setCellValue('BH8', 'Medical');
                    $sheet->setCellValue('BH9', 'Fee');
                    $sheet->setCellValue('BH10', '');
                    $sheet->setCellValue('BB12', '()');

                    $sheet->setCellValue('BJ7', 'DATE');
                    $sheet->setCellValue('BK7', 'SCHOOL FEES');
                    $sheet->setCellValue('BK8', 'Laboratory');
                    $sheet->setCellValue('BK9', 'Fee');
                    $sheet->setCellValue('BK10', '');
                    $sheet->setCellValue('BL8', 'Transcript');
                    $sheet->setCellValue('BL9', 'of Records');
                    $sheet->setCellValue('BL10', '');
                    $sheet->setCellValue('BM8', 'Transcript of');
                    $sheet->setCellValue('BM9', 'Records (scan)');
                    $sheet->setCellValue('BM10', '');
                    $sheet->setCellValue('BK12', '()');
                    $sheet->setCellValue('BN8', 'Comprehensive');
                    $sheet->setCellValue('BN9', 'Examination');
                    $sheet->setCellValue('BN10', 'Fee');
                    $sheet->setCellValue('BN12', '()');
                    $sheet->setCellValue('BO9', 'Rent Income/');
                    $sheet->setCellValue('BO10', 'Canteen');
                    $sheet->setCellValue('BO12', '()');
                    $sheet->setCellValue('BP8', 'Rental');
                    $sheet->setCellValue('BP10', '');
                    $sheet->setCellValue('BQ8', 'Electricity/Water');
                    $sheet->setCellValue('BQ10', '');
                    $sheet->setCellValue('BR8', 'Facilities');
                    $sheet->setCellValue('BR10', '');
                    $sheet->setCellValue('BS8', 'Book Rental');
                    $sheet->setCellValue('BS10', '');
                    $sheet->setCellValue('BT8', 'Miscellaneous');
                    $sheet->setCellValue('BT10', '');
                    $sheet->setCellValue('BP12', '()');
                    $sheet->setCellValue('BU8', 'List of Graduates');
                    $sheet->setCellValue('BU12', '()');
                    $sheet->setCellValue('BV7', 'OTHER BUSINESS INCOME');
                    $sheet->setCellValue('BV8', 'Photo');
                    $sheet->setCellValue('BV10', '');
                    $sheet->setCellValue('BW8', 'Toga');
                    $sheet->setCellValue('BW10', '');
                    $sheet->setCellValue('BX8', 'Books');
                    $sheet->setCellValue('BX10', '');
                    $sheet->setCellValue('BY8', 'Medical');
                    $sheet->setCellValue('BY9', 'Exam');
                    $sheet->setCellValue('BY10', '');
                    $sheet->setCellValue('BZ8', 'PE Uniform');
                    $sheet->setCellValue('BZ10', '');
                    $sheet->setCellValue('BV12', '()');
                    $sheet->setCellValue('CA7', 'OTHERS');
                    $sheet->setCellValue('CA9', 'Account');
                    $sheet->setCellValue('CA10', 'Description');
                    $sheet->setCellValue('CB9', 'UACS');
                    $sheet->setCellValue('CB10', 'Object');
                    $sheet->setCellValue('CB11', 'Code');
                    $sheet->setCellValue('CC9', 'Amount');

                    //Merges
                    $sheet->mergeCells('B1:J1');
                    $sheet->mergeCells('J3:O3');
                    $sheet->mergeCells('A7:A12');
                    $sheet->mergeCells('H7:T7');
                    $sheet->mergeCells('H8:H9');
                    $sheet->mergeCells('H10:H11');
                    $sheet->mergeCells('I10:I11');
                    $sheet->mergeCells('J10:J11');
                    $sheet->mergeCells('K8:K9');
                    $sheet->mergeCells('K10:K11');
                    $sheet->mergeCells('L10:L11');
                    $sheet->mergeCells('M10:M11');
                    $sheet->mergeCells('N8:N9');
                    $sheet->mergeCells('N10:N11');
                    $sheet->mergeCells('O10:O11');
                    $sheet->mergeCells('P10:P11');
                    $sheet->mergeCells('Q10:Q11');
                    $sheet->mergeCells('R8:R9');
                    $sheet->mergeCells('R10:R11');
                    $sheet->mergeCells('S8:S9');
                    $sheet->mergeCells('S10:S11');
                    $sheet->mergeCells('T8:T9');
                    $sheet->mergeCells('T10:T11');
                    $sheet->mergeCells('H12:T12');

                    $sheet->mergeCells('V7:V11');
                    $sheet->mergeCells('W7:AL7');
                    $sheet->mergeCells('W8:W9');
                    $sheet->mergeCells('W10:W11');
                    $sheet->mergeCells('X8:X9');
                    $sheet->mergeCells('X10:X11');
                    $sheet->mergeCells('Y8:Y9');
                    $sheet->mergeCells('Y10:Y11');
                    $sheet->mergeCells('Z8:Z9');
                    $sheet->mergeCells('Z10:Z11');
                    $sheet->mergeCells('AA10:AA11');
                    $sheet->mergeCells('AB8:AB9');
                    $sheet->mergeCells('AB10:AB11');
                    $sheet->mergeCells('AC8:AC9');
                    $sheet->mergeCells('AC10:AC11');
                    $sheet->mergeCells('AD10:AD11');
                    $sheet->mergeCells('AE10:AE11');
                    $sheet->mergeCells('AF10:AF11');
                    $sheet->mergeCells('AG8:AG9');
                    $sheet->mergeCells('AG10:AG11');
                    $sheet->mergeCells('AH8:AH9');
                    $sheet->mergeCells('AH10:AH11');
                    $sheet->mergeCells('AI10:AI11');
                    $sheet->mergeCells('AJ10:AJ11');
                    $sheet->mergeCells('AK8:AK9');
                    $sheet->mergeCells('AK10:AK11');
                    $sheet->mergeCells('AL10:AL11');
                    $sheet->mergeCells('W12:AL12');

                    $sheet->mergeCells('AO7:AO11');
                    $sheet->mergeCells('AP7:AW7');
                    $sheet->mergeCells('AP8:AP9');
                    $sheet->mergeCells('AP10:AP11');
                    $sheet->mergeCells('AQ8:AQ9');
                    $sheet->mergeCells('AQ10:AQ11');
                    $sheet->mergeCells('AR8:AR9');
                    $sheet->mergeCells('AR10:AR11');
                    $sheet->mergeCells('AS8:AS9');
                    $sheet->mergeCells('AS10:AS11');
                    $sheet->mergeCells('AT8:AT11');
                    $sheet->mergeCells('AZ8:AZ11');
                    $sheet->mergeCells('AB10:AB11');
                    $sheet->mergeCells('AP12:AR12');
                    $sheet->mergeCells('BB7:BH7');
                    $sheet->mergeCells('BB8:BB9');
                    $sheet->mergeCells('BB10:BB11');
                    $sheet->mergeCells('BC10:BC11');
                    $sheet->mergeCells('BD10:BD11');
                    $sheet->mergeCells('BE8:BE9');
                    $sheet->mergeCells('BE10:BE11');
                    $sheet->mergeCells('BF10:BF11');
                    $sheet->mergeCells('BG10:BG11');
                    $sheet->mergeCells('BH10:BH11');
                    $sheet->mergeCells('BB12:BH12');

                    $sheet->mergeCells('BJ7:BJ11');
                    $sheet->mergeCells('BK7:BM7');
                    $sheet->mergeCells('BK10:BK11');
                    $sheet->mergeCells('BL10:BL11');
                    $sheet->mergeCells('BM10:BM11');
                    $sheet->mergeCells('BK12:BM12');
                    $sheet->mergeCells('BP8:BP9');
                    $sheet->mergeCells('BP10:BP11');
                    $sheet->mergeCells('BQ8:BQ9');
                    $sheet->mergeCells('BQ10:BQ11');
                    $sheet->mergeCells('BR8:BR9');
                    $sheet->mergeCells('BR10:BR11');
                    $sheet->mergeCells('BS8:BS9');
                    $sheet->mergeCells('BS10:BS11');
                    $sheet->mergeCells('BT8:BT9');
                    $sheet->mergeCells('BT10:BT11');
                    $sheet->mergeCells('BR12:BT12');
                    $sheet->mergeCells('BU8:BU11');
                    $sheet->mergeCells('BV7:BZ8');
                    $sheet->mergeCells('BV8:BV9');
                    $sheet->mergeCells('BV10:BV11');
                    $sheet->mergeCells('BW8:BW9');
                    $sheet->mergeCells('BW10:BW11');
                    $sheet->mergeCells('BX8:BX9');
                    $sheet->mergeCells('BX10:BX11');
                    $sheet->mergeCells('BY8:BY9');
                    $sheet->mergeCells('BY10:BY11');
                    $sheet->mergeCells('BZ8:BZ9');
                    $sheet->mergeCells('BZ10:BZ11');
                    $sheet->mergeCells('BV12:BZ12');
                    $sheet->mergeCells('CA7:CC7');

                    $sheet->getStyle('B1')
                        ->applyFromArray([
                            'font' => [
                                'name' => 'Courier',
                                'size' => 14,
                                'bold' => true,
                            ],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_CENTER,
                                'vertical' => Alignment::VERTICAL_CENTER
                            ]
                        ]);

                    $sheet->getStyle('J3')
                        ->applyFromArray([
                            'font' => [
                                'name' => 'Californian FB',
                                'size' => 10,
                                'bold' => true,
                            ],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_CENTER,
                                'vertical' => Alignment::VERTICAL_CENTER
                            ]
                        ]);

                    $sheet->getStyle('B5:M5')
                        ->applyFromArray([
                            'font' => [
                                'name' => 'Courier',
                                'size' => 11,
                                'bold' => true,
                            ],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_CENTER,
                                'vertical' => Alignment::VERTICAL_CENTER
                            ]
                        ]);

                    $sheet->getStyle('A7:CC11')
                        ->applyFromArray([
                            'font' => [
                                'name' => 'Courier',
                                'size' => 8
                            ],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_CENTER,
                                'vertical' => Alignment::VERTICAL_CENTER
                            ]
                        ]);

                    $sheet->getStyle('A12:CC12')
                        ->applyFromArray([
                            'font' => [
                                'name' => 'Times New Roman',
                                'size' => 8
                            ],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_CENTER,
                                'vertical' => Alignment::VERTICAL_CENTER
                            ]
                        ]);

                    //For all outline Borders 
                    $OutlineCells = [
                        'A7:A12',
                        'B7:B12',
                        'C7:C12',
                        'D7:D11',
                        'E7:E11',
                        'F7:F11',
                        'G7:G11',
                        'H7:T7',
                        'H8:H9',
                        'I8:I9',
                        'J8:J9',
                        'K8:K9',
                        'L8:L9',
                        'M8:M9',
                        'N8:N9',
                        'O8:O9',
                        'P8:P9',
                        'Q8:Q9',
                        'R8:R9',
                        'S8:S9',
                        'T8:T9',
                        'H12:T12',
                        'W7:AL7',
                        'W8:W9',
                        'X8:X9',
                        'Y8:Y9',
                        'Z8:Z9',
                        'AA8:AA9',
                        'AB8:AB9',
                        'AC8:AC9',
                        'AD8:AD9',
                        'AE8:AE9',
                        'AF8:AF9',
                        'AG8:AG9',
                        'AH8:AH9',
                        'AG8:AG9',
                        'AH8:AH9',
                        'AI8:AI9',
                        'AJ8:AJ9',
                        'AK8:AK9',
                        'AL8:AL9',
                        'W12:AL12',
                        'AP7:AW7',
                        'AX7:AY7',
                        'AS8:AS11',
                        'AT8:AT11',
                        'AU8:AU11',
                        'AV8:AV11',
                        'AW8:AW11',
                        'AX8:AX11',
                        'AY8:AY11',
                        'AZ8:AZ11',
                        'BA8:BA11',
                        'BB8:BB9',
                        'BC8:BC9',
                        'BD8:BD9',
                        'BE8:BE9',
                        'BF8:BF9',
                        'BG8:BG9',
                        'BH8:BH9',
                        'BK8:BK9',
                        'BL8:BL9',
                        'BM8:BM9',
                        'BN8:BN11',
                        'BO8:BO11',
                        'BY8:BY9',
                        'BZ8:BZ9',
                        'CA8:CA11',
                        'CB8:CB11',
                        'CC8:CC11',
                        'CA12:CC12',
                    ];

                    foreach ($OutlineCells as $range) {

                        $sheet->getStyle($range)
                            ->applyFromArray([
                                'borders' => [
                                    'outline' => [
                                        'borderStyle' => Border::BORDER_MEDIUM
                                    ]
                                ]
                            ]);
                    }

                    $AllBorderCells = [
                        'D12:G12',
                        'H10:T11',
                        'V7:V12',
                        'W10:AL11',
                        'AO7:AO12',
                        'AP7:AR12',
                        'AS12:BH12',
                        'AZ7:BH7',
                        'BB10:BH11',
                        'BJ7:BJ12',
                        'BK7:CC7',
                        'BK10:BM11',
                        'BP8:BX11',
                        'BY10:BZ11',
                        'BK12:BZ12',
                    ];

                    foreach ($AllBorderCells as $range) {

                        $sheet->getStyle($range)
                            ->applyFromArray([
                                'borders' => [
                                    'allBorders' => [
                                        'borderStyle' => Border::BORDER_MEDIUM
                                    ]
                                ]
                            ]);
                    }

                }
            
        ];
    }
}
