<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\ToArray;

class HeadingRowImport implements ToArray, WithHeadingRow
{
    public function array(array $array)
    {
        // Tidak perlu implementasi khusus jika hanya untuk toArray
    }
}