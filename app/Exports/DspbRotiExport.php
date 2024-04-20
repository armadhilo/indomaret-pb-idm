<?php

namespace App\Exports;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class DspbRotiExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $check;

    public function __construct($check)
    {
        $this->check = $check;
    }

    public function collection()
    {
        // Convert stdClass object to an associative array
        $rowData = json_decode(json_encode($this->check), true);

        // Wrap the associative array inside a collection
        return collect([$rowData]);
    }

    public function headings(): array
    {
        $firstRow = collect($this->check)->first();
        return array_map('strtoupper', array_keys((array) $firstRow));
    }
}
