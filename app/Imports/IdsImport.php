<?php

namespace App\Imports;

class IdsImport
{
    public function model(array $row)
    {
        return [
            'phone' => $row[0],
            'amount' => $row[1],
            'coins' => $row[2],
        ];
    }
}
