<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class GeneralExcelImport implements ToCollection
{
    /**
    * @param Collection $collection
    */
    public $data;

    public function collection(Collection $rows)
    {
        $headers = $rows->first()->toArray(); // Get the headers from the first row
        $data = [];

        foreach ($rows->skip(1) as $row) {
            $rowData = [];
            foreach ($row as $key => $value) {
                $rowData[$headers[$key]] = $value; // Use headers as keys
            }
            $data[] = $rowData;
        }

        $this->data = $data;
    }
}
