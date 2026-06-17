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

class CollectionsReportSummary implements FromArray, WithEvents, WithTitle, WithColumnWidths
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
        return $this->month->format('FY') . "Summary";
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

                    $sheet->setCellValue('C1', 'REPORT OF COLLECTIONS');
                    $sheet->setCellValue('K3', strtoupper($this->startDate->format('F j Y')) . ' TO ' . strtoupper($this->endDate->format('F j Y')));
                    $sheet->setCellValue('B5', 'PUP TAGUIG CAMPUS');
                    $sheet->setCellValue('M5', 'TAGUIG CITY');
                    $sheet->setCellValue('T5', 'xxxxxxxxxx');
                    $sheet->setCellValue('AL5', 'xxxxxxxxxxx');
                    $sheet->setCellValue('BH5', 'xxxxxxxxxx');
                    $sheet->setCellValue('CD5', 'xxxxxxxxx');
                    $sheet->setCellValue('A6', 'DATE');
                    $sheet->setCellValue('B7', 'OFFICIAL');
                    $sheet->setCellValue('B8', 'RECEIPT');
                    $sheet->setCellValue('B9', 'NUMBER');
                    $sheet->setCellValue('C7', 'TOTAL');
                    $sheet->setCellValue('C9', 'COLLECTION');
                    $sheet->setCellValue('D7', 'Certification');
                    $sheet->setCellValue('D8', 'Fee');
                    $sheet->setCellValue('D11', "(402010400000000)");
                    $sheet->setCellValue('E7', 'Certification');
                    $sheet->setCellValue('E9', 'of Grades');
                    $sheet->setCellValue('E11', "(40201990000057)");
                    $sheet->setCellValue('F7', 'Certification');
                    $sheet->setCellValue('F9', 'of Documents');
                    $sheet->setCellValue('F11', "(40201990000058)");
                    $sheet->setCellValue('G7', 'Fines & ');
                    $sheet->setCellValue('G8', 'Penalties');
                    $sheet->setCellValue('G9', 'Service Income');
                    $sheet->setCellValue('G11', "(402011400000000)");
                    $sheet->setCellValue('H6', 'OTHER SERVICE INCOME');
                    $sheet->setCellValue('H7', 'ID');
                    $sheet->setCellValue('H9', '00001');
                    $sheet->setCellValue('I7', 'Entrance');
                    $sheet->setCellValue('I8', 'Exam');
                    $sheet->setCellValue('I9', '00002');
                    $sheet->setCellValue('J7', 'English Plus');
                    $sheet->setCellValue('J8', 'Test');
                    $sheet->setCellValue('J9', '00003');
                    $sheet->setCellValue('K7', 'Module');
                    $sheet->setCellValue('K9', '00004');
                    $sheet->setCellValue('L7', 'NSTP');
                    $sheet->setCellValue('L8', 'Module');
                    $sheet->setCellValue('L9', '00005');
                    $sheet->setCellValue('M7', 'Graduation');
                    $sheet->setCellValue('M8', 'Forum');
                    $sheet->setCellValue('M9', '00006');
                    $sheet->setCellValue('N7', 'Publication');
                    $sheet->setCellValue('N9', '00007');
                    $sheet->setCellValue('O7', 'Income from');
                    $sheet->setCellValue('O8', 'IT');
                    $sheet->setCellValue('O9', '00008');
                    $sheet->setCellValue('P7', 'Publication');
                    $sheet->setCellValue('P8', 'Fees');
                    $sheet->setCellValue('P9', '00009');
                    $sheet->setCellValue('Q7', 'Permit');
                    $sheet->setCellValue('Q8', 'Fee');
                    $sheet->setCellValue('Q9', '00020');
                    $sheet->setCellValue('R7', 'Duplicate');
                    $sheet->setCellValue('R9', '00021');
                    $sheet->setCellValue('S7', 'HD');
                    $sheet->setCellValue('S9', '00022');
                    $sheet->setCellValue('T7', 'Completion');
                    $sheet->setCellValue('T9', '00023');
                    $sheet->setCellValue('H11', "(40201990990000)");

                    $sheet->setCellValue('V6', 'DATE');
                    $sheet->setCellValue('W6', 'OTHER SERVICE INCOME');
                    $sheet->setCellValue('W7', 'Readmission');
                    $sheet->setCellValue('W9', '00024');
                    $sheet->setCellValue('X7', 'Retrieval');
                    $sheet->setCellValue('X9', '00025');
                    $sheet->setCellValue('Y7', 'Handbook');
                    $sheet->setCellValue('Y9', '00026');
                    $sheet->setCellValue('Z7', 'Scannable');
                    $sheet->setCellValue('Z9', '00027');
                    $sheet->setCellValue('AA7', 'Change Subject/');
                    $sheet->setCellValue('AA8', 'Curriculum/Shifting');
                    $sheet->setCellValue('AA9', '00028');
                    $sheet->setCellValue('AB7', 'Accreditation');
                    $sheet->setCellValue('AB9', '00029');
                    $sheet->setCellValue('AC7', 'Evaluation');
                    $sheet->setCellValue('AC9', '00030');
                    $sheet->setCellValue('AD7', 'Guidance');
                    $sheet->setCellValue('AD8', 'Fee');
                    $sheet->setCellValue('AD9', '00031');
                    $sheet->setCellValue('AE7', 'Psychological');
                    $sheet->setCellValue('AE8', 'Exam');
                    $sheet->setCellValue('AE9', '00032');
                    $sheet->setCellValue('AF7', 'Developmental');
                    $sheet->setCellValue('AF8', 'Fees');
                    $sheet->setCellValue('AF9', '00033');
                    $sheet->setCellValue('AG7', 'SIS');
                    $sheet->setCellValue('AG9', '00034');
                    $sheet->setCellValue('AH7', 'ITD');
                    $sheet->setCellValue('AH9', '00035');
                    $sheet->setCellValue('AI7', 'Authentication');
                    $sheet->setCellValue('AI8', 'Fee');
                    $sheet->setCellValue('AI9', '00036');
                    $sheet->setCellValue('AJ7', 'Sports');
                    $sheet->setCellValue('AJ8', 'Development');
                    $sheet->setCellValue('AJ9', '00037');
                    $sheet->setCellValue('AK7', 'Deposit');
                    $sheet->setCellValue('AK9', '00038');
                    $sheet->setCellValue('AL7', 'Energy');
                    $sheet->setCellValue('AL8', 'Fee');
                    $sheet->setCellValue('AL9', '00041');
                    $sheet->setCellValue('W11', '(40201990990000)');

                    $sheet->setCellValue('AO6', 'DATE');
                    $sheet->setCellValue('AP6', 'OTHER SERVICE INCOME');
                    $sheet->setCellValue('AP7', 'Tutorial');
                    $sheet->setCellValue('AP9', '00042');
                    $sheet->setCellValue('AQ7', 'Memorabilia');
                    $sheet->setCellValue('AQ9', '00043');
                    $sheet->setCellValue('AR7', 'Verification');
                    $sheet->setCellValue('AR9', '00044');
                    $sheet->setCellValue('AP11', '(40201990990000)');
                    $sheet->setCellValue('AS7', 'Documentary');
                    $sheet->setCellValue('AS9', 'Stamp');
                    $sheet->setCellValue('AS11', '(40201990000059)');
                    $sheet->setCellValue('AT7', 'Insurance');
                    $sheet->setCellValue('AT11', '(40201990000060)');
                    $sheet->setCellValue('AU7', 'Participation');
                    $sheet->setCellValue('AU8', 'in');
                    $sheet->setCellValue('AU9', 'Sports');
                    $sheet->setCellValue('AU10', 'Competition');
                    $sheet->setCellValue('AU11', '(40201990000061)');
                    $sheet->setCellValue('AV7', 'Sports');
                    $sheet->setCellValue('AV8', 'Related');
                    $sheet->setCellValue('AV9', 'Training');
                    $sheet->setCellValue('AV10', 'Fee');
                    $sheet->setCellValue('AV11', '402019909900086');
                    $sheet->setCellValue('AV7', 'Sports');
                    $sheet->setCellValue('AV8', 'Participation');
                    $sheet->setCellValue('AV9', 'Fee');
                    $sheet->setCellValue('AV11', '402019909900087');
                    $sheet->setCellValue('AX8', 'Catalyst');
                    $sheet->setCellValue('AX9', 'Fee');
                    $sheet->setCellValue('AY7', 'Student');
                    $sheet->setCellValue('AY8', 'Council');
                    $sheet->setCellValue('AY9', 'Fee');
                    $sheet->setCellValue('AY11', '(402019909900088)');
                    $sheet->setCellValue('AZ7', 'Tuition Fees');
                    $sheet->setCellValue('AZ11', '(402020100100000)');
                    $sheet->setCellValue('BA7', 'Tuition');
                    $sheet->setCellValue('BA8', 'Fees');
                    $sheet->setCellValue('BA10', 'NSTP');
                    $sheet->setCellValue('BA11', '(402020100100001)');
                    $sheet->setCellValue('BB6', 'SCHOOL FEES');
                    $sheet->setCellValue('BB7', 'Cultural');
                    $sheet->setCellValue('BB9', '00001');
                    $sheet->setCellValue('BC7', 'Athletic');
                    $sheet->setCellValue('BC8', 'Fees');
                    $sheet->setCellValue('BC9', '00002');
                    $sheet->setCellValue('BD7', 'Athletic');
                    $sheet->setCellValue('BD8', 'Development');
                    $sheet->setCellValue('BD9', '00003');
                    $sheet->setCellValue('BE7', 'Diploma');
                    $sheet->setCellValue('BE9', '00004');
                    $sheet->setCellValue('BF7', 'Graduation');
                    $sheet->setCellValue('BF8', 'Fee');
                    $sheet->setCellValue('BF9', '00005');
                    $sheet->setCellValue('BG7', 'Library');
                    $sheet->setCellValue('BG8', 'Fee');
                    $sheet->setCellValue('BG9', '00006');
                    $sheet->setCellValue('BH7', 'Medical');
                    $sheet->setCellValue('BH8', 'Fee');
                    $sheet->setCellValue('BH9', '00007');
                    $sheet->setCellValue('BB11', '(402020109900000)');

                    $sheet->setCellValue('BK6', 'DATE');
                    $sheet->setCellValue('BL6', 'SCHOOL FEES');
                    $sheet->setCellValue('BL7', 'Laboratory');
                    $sheet->setCellValue('BL8', 'Fee');
                    $sheet->setCellValue('BL9', '00008');
                    $sheet->setCellValue('BM7', 'Transcript');
                    $sheet->setCellValue('BM8', 'of Records');
                    $sheet->setCellValue('BM9', '00009');
                    $sheet->setCellValue('BN7', 'Transcript of');
                    $sheet->setCellValue('BN8', 'Records (scan)');
                    $sheet->setCellValue('BN9', '00010');
                    $sheet->setCellValue('BL11', '(402020109900000)');
                    $sheet->setCellValue('BO7', 'Comprehensive');
                    $sheet->setCellValue('BO8', 'Examination');
                    $sheet->setCellValue('BO9', 'Fee');
                    $sheet->setCellValue('BO11', '(402020300000000)');
                    $sheet->setCellValue('BP8', 'Rent Income/');
                    $sheet->setCellValue('BP9', 'Canteen');
                    $sheet->setCellValue('BP11', '(402020500000000)');
                    $sheet->setCellValue('BQ7', 'Rental');
                    $sheet->setCellValue('BQ9', '00001');
                    $sheet->setCellValue('BR7', 'Electricity/Water');
                    $sheet->setCellValue('BR9', '00005');
                    $sheet->setCellValue('BS7', 'Facilities');
                    $sheet->setCellValue('BS9', '00006');
                    $sheet->setCellValue('BT7', 'Book Rental');
                    $sheet->setCellValue('BT9', '00007');
                    $sheet->setCellValue('BU7', 'Miscellaneous');
                    $sheet->setCellValue('BU9', '00008');
                    $sheet->setCellValue('BQ11', '(402020500)');
                    $sheet->setCellValue('BV7', 'List of Graduates');
                    $sheet->setCellValue('BV11', '(405019900000002)');
                    $sheet->setCellValue('BW6', 'OTHER BUSINESS INCOME');
                    $sheet->setCellValue('BW7', 'Photo');
                    $sheet->setCellValue('BW9', '00001');
                    $sheet->setCellValue('BX7', 'Toga');
                    $sheet->setCellValue('BX9', '00003');
                    $sheet->setCellValue('BY7', 'Books');
                    $sheet->setCellValue('BY9', '00004');
                    $sheet->setCellValue('BZ7', 'Medical');
                    $sheet->setCellValue('BZ8', 'Exam');
                    $sheet->setCellValue('BZ9', '00005');
                    $sheet->setCellValue('CA7', 'PE Uniform');
                    $sheet->setCellValue('CA9', '00006');
                    $sheet->setCellValue('BW11', '(4020299099)');
                    $sheet->setCellValue('CB6', 'OTHERS');
                    $sheet->setCellValue('CB8', 'Account');
                    $sheet->setCellValue('CB9', 'Description');
                    $sheet->setCellValue('CC8', 'UACS');
                    $sheet->setCellValue('CC9', 'Object');
                    $sheet->setCellValue('CC10', 'Code');
                    $sheet->setCellValue('CD8', 'Amount');

                    //Merges
                    $sheet->mergeCells('K3:P3');
                    $sheet->mergeCells('A6:A11');
                    $sheet->mergeCells('H6:T6');
                    $sheet->mergeCells('H7:H8');
                    $sheet->mergeCells('H9:H10');
                    $sheet->mergeCells('I9:I10');
                    $sheet->mergeCells('J9:J10');
                    $sheet->mergeCells('K7:K8');
                    $sheet->mergeCells('K9:K10');
                    $sheet->mergeCells('L9:L10');
                    $sheet->mergeCells('M9:M10');
                    $sheet->mergeCells('N7:N8');
                    $sheet->mergeCells('N9:N10');
                    $sheet->mergeCells('O9:O10');
                    $sheet->mergeCells('P9:P10');
                    $sheet->mergeCells('Q9:Q10');
                    $sheet->mergeCells('R7:R8');
                    $sheet->mergeCells('R9:R10');
                    $sheet->mergeCells('S7:S8');
                    $sheet->mergeCells('S9:S10');
                    $sheet->mergeCells('T7:T8');
                    $sheet->mergeCells('T9:T10');
                    $sheet->mergeCells('H11:T11');

                    $sheet->mergeCells('V6:V10');
                    $sheet->mergeCells('W6:AL6');
                    $sheet->mergeCells('W7:W8');
                    $sheet->mergeCells('W9:W10');
                    $sheet->mergeCells('X7:X8');
                    $sheet->mergeCells('X9:X10');
                    $sheet->mergeCells('Y7:Y8');
                    $sheet->mergeCells('Y9:Y10');
                    $sheet->mergeCells('Z7:Z8');
                    $sheet->mergeCells('Z9:Z10');
                    $sheet->mergeCells('AA9:AA10');
                    $sheet->mergeCells('AB7:AB8');
                    $sheet->mergeCells('AB9:AB10');
                    $sheet->mergeCells('AC7:AC8');
                    $sheet->mergeCells('AC9:AC10');
                    $sheet->mergeCells('AD9:AD10');
                    $sheet->mergeCells('AE9:AE10');
                    $sheet->mergeCells('AF9:AF10');
                    $sheet->mergeCells('AG7:AG8');
                    $sheet->mergeCells('AG9:AG10');
                    $sheet->mergeCells('AH7:AH8');
                    $sheet->mergeCells('AH9:AH10');
                    $sheet->mergeCells('AI9:AI10');
                    $sheet->mergeCells('AJ9:AJ10');
                    $sheet->mergeCells('AK7:AK8');
                    $sheet->mergeCells('AK9:AK10');
                    $sheet->mergeCells('AL9:AL10');
                    $sheet->mergeCells('W11:AL11');

                    $sheet->mergeCells('AO6:AO10');
                    $sheet->mergeCells('AP6:AW6');
                    $sheet->mergeCells('AP7:AP8');
                    $sheet->mergeCells('AP9:AP10');
                    $sheet->mergeCells('AQ7:AQ8');
                    $sheet->mergeCells('AQ9:AQ10');
                    $sheet->mergeCells('AR7:AR8');
                    $sheet->mergeCells('AR9:AR10');
                    $sheet->mergeCells('AS7:AS8');
                    $sheet->mergeCells('AS9:AS10');
                    $sheet->mergeCells('AT7:AT10');
                    $sheet->mergeCells('AZ7:AZ10');
                    $sheet->mergeCells('AB9:AB10');
                    $sheet->mergeCells('AP11:AR11');
                    $sheet->mergeCells('BB6:BH6');
                    $sheet->mergeCells('BB7:BB8');
                    $sheet->mergeCells('BB9:BB10');
                    $sheet->mergeCells('BC9:BC10');
                    $sheet->mergeCells('BD9:BD10');
                    $sheet->mergeCells('BE7:BE8');
                    $sheet->mergeCells('BE9:BE10');
                    $sheet->mergeCells('BF9:BF10');
                    $sheet->mergeCells('BG9:BG10');
                    $sheet->mergeCells('BH9:BH10');
                    $sheet->mergeCells('BB11:BH11');

                    $sheet->mergeCells('BK6:BK10');
                    $sheet->mergeCells('BL6:BN6');
                    $sheet->mergeCells('BL9:BL10');
                    $sheet->mergeCells('BM9:BM10');
                    $sheet->mergeCells('BN9:BN10');
                    $sheet->mergeCells('BL11:BN11');
                    $sheet->mergeCells('BQ7:BQ8');
                    $sheet->mergeCells('BQ9:BQ10');
                    $sheet->mergeCells('BR7:BR8');
                    $sheet->mergeCells('BR9:BR10');
                    $sheet->mergeCells('BS7:BS8');
                    $sheet->mergeCells('BS9:BS10');
                    $sheet->mergeCells('BT7:BT8');
                    $sheet->mergeCells('BT9:BT10');
                    $sheet->mergeCells('BU7:BU8');
                    $sheet->mergeCells('BU9:BU10');
                    $sheet->mergeCells('BQ11:BU11');
                    $sheet->mergeCells('BV7:BV10');
                    $sheet->mergeCells('BW6:CA6');
                    $sheet->mergeCells('BW7:BW8');
                    $sheet->mergeCells('BW9:BW10');
                    $sheet->mergeCells('BX7:BX8');
                    $sheet->mergeCells('BX9:BX10');
                    $sheet->mergeCells('BY7:BY8');
                    $sheet->mergeCells('BY9:BY10');
                    $sheet->mergeCells('BZ7:BZ8');
                    $sheet->mergeCells('BZ9:BZ10');
                    $sheet->mergeCells('CA7:CA8');
                    $sheet->mergeCells('CA9:CA10');
                    $sheet->mergeCells('BW11:CA11');
                    $sheet->mergeCells('CB6:CD6');

                    $sheet->getStyle('C1')
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

                    $sheet->getStyle('K3')
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

                    $sheet->getStyle('A6:CD10')
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

                    $sheet->getStyle('A11:CD11')
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
                        'A6:A11',
                        'B6:B11',
                        'C6:C11',
                        'D6:D10',
                        'E6:E10',
                        'F6:F10',
                        'G6:G10',
                        'H6:T6',
                        'H7:H8',
                        'I7:I8',
                        'J7:J8',
                        'K7:K8',
                        'L7:L8',
                        'M7:M8',
                        'N7:N8',
                        'O7:O8',
                        'P7:P8',
                        'Q7:Q8',
                        'R7:R8',
                        'S7:S8',
                        'T7:T8',
                        'H11:T11',
                        'W6:AL6',
                        'W7:W8',
                        'X7:X8',
                        'Y7:Y8',
                        'Z7:Z8',
                        'AA7:AA8',
                        'AB7:AB8',
                        'AC7:AC8',
                        'AD7:AD8',
                        'AE7:AE8',
                        'AF7:AF8',
                        'AG7:AG8',
                        'AH7:AH8',
                        'AG7:AG8',
                        'AH7:AH8',
                        'AI7:AI8',
                        'AJ7:AJ8',
                        'AK7:AK8',
                        'AL7:AL8',
                        'W11:AL11',
                        'AP6:AW6',
                        'AX6:AY6',
                        'AS7:AS10',
                        'AT7:AT10',
                        'AU7:AU10',
                        'AV7:AV10',
                        'AW7:AW10',
                        'AX7:AX10',
                        'AY7:AY10',
                        'AZ7:AZ10',
                        'BA7:BA10',
                        'BB7:BB8',
                        'BC7:BC8',
                        'BD7:BD8',
                        'BE7:BE8',
                        'BF7:BF8',
                        'BG7:BG8',
                        'BH7:BH8',
                        'BL7:BL8',
                        'BM7:BM8',
                        'BN7:BN8',
                        'BO7:BO10',
                        'BP7:BP10',
                        'BZ7:BZ8',
                        'CA7:CA8',
                        'CB7:CB10',
                        'CC7:CC10',
                        'CD7:CD10',
                        'CB11:CD11',
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
                        'D11:G11',
                        'H9:T10',
                        'V6:V11',
                        'W9:AL10',
                        'AO6:AO11',
                        'AP6:AR11',
                        'AS11:BH11',
                        'AZ6:BH6',
                        'BB9:BH10',
                        'BK6:BK11',
                        'BL6:CD6',
                        'BL9:BN10',
                        'BQ7:BY10',
                        'BZ9:CA10',
                        'BL11:CA11',
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
