<?php

namespace App\Traits;

trait ExcelHelpers
{
    // метод установливает авто размер в зависимости от заданного условия
    public function setAutoSizeColumn($sheet, $boolean = false, ...$columns)
    {
        foreach($columns as $column) {
            $sheet->getColumnDimension($column)->setAutoSize($boolean);
        }
    }

    // метод установливает выравнение (горизантально) по ячейке в зависимости от заданного условия
    public function setHorizontal($sheet, $position, ...$columns)
    {
        foreach($columns as $column) {
            $sheet->getStyle($column)->getAlignment()->setHorizontal($position);
        }
    }
}
