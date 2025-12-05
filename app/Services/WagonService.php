<?php

namespace App\Services;

use App\Models\Container;
use App\Models\User;
use App\Models\Wagon\Wagon;
use App\Models\Wagon\WagonLog;
use App\Models\Wagon\WagonStock;
use App\Models\Wagon\WagonStockContainer;
use App\Models\Wagon\WagonTaskItem;
use App\Models\Wagon\WagonTaskItemContainer;
use App\Models\Wagon\WagonTrack;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use App\Traits\ExcelHelpers;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class WagonService
{
    use ExcelHelpers;

    protected array $operationArr = [
        'Выгрузка' => 'unloading',
        'Погрузка' => 'loading',
        'Растаможка' => 'customs',
        'Ничего' => 'nothing',
    ];

    public function createWagonTaskForSubmission($wagonTask, $sheetData, User $user): array
    {
        $errors = [];
        $createdCount = 0;

        $wagonNumbers = collect($sheetData)->pluck('C')->filter()->unique()->values()->toArray();
        $trackNumbers = collect($sheetData)->pluck('H')->filter()->unique()->values()->toArray();
        $destTrackNumbers = collect($sheetData)->pluck('I')->filter()->unique()->values()->toArray();

        $wagonsMap = Wagon::whereIn('number', $wagonNumbers)->get()->keyBy('number');
        $tracksMap = WagonTrack::whereIn('number', array_unique(array_merge($trackNumbers, $destTrackNumbers)))->get()->keyBy('number');

        foreach ($sheetData as $key=>$arr) {
            if ($key == 1) continue;
            $ordinalNumber = $arr['A'];
            $receptionDatetime = $arr['B'];
            $wagonNumber = $arr['C'];
            $wagonLength = $arr['D'];
            $containerNumber = (empty($arr['E'])) ? null : $arr['E'];
            $containerType = (empty($arr['F'])) ? '40' : $arr['F'];
            $state = $arr['G'];
            $exhibitionTrack = $arr['H'];
            $destinationTrack = $arr['I'];
            $countryCode = (empty($arr['J'])) ? null : $arr['J'];
            $ownerName = (empty($arr['K'])) ? null : $arr['K'];
            $cargoName = (empty($arr['L'])) ? null : $arr['L'];
            $consigneeName = (empty($arr['M'])) ? null : $arr['M'];
            $operationType = $arr['N'];

            if(strlen($receptionDatetime) == 0) break;

            if($wagonNumber != '*' && strlen($wagonNumber) == 0) {
                break;
            };

            if($wagonNumber == '*') {
                $prevWagonNumber = $this->findPreviousWagonNumber($sheetData, $key);

                if(!$prevWagonNumber) {
                    $errors[] = "Не найден номер вагона для строки {$key} (используется '*')";
                    continue;
                }
                $wagon = $wagonsMap->get($prevWagonNumber) ?? Wagon::where('number', $prevWagonNumber)->first();
                if(!$wagon) {
                    $errors[] = "Вагон с номером {$prevWagonNumber} не найден в базе (строка {$key})";
                    continue;
                }
            } else {
                $wagon = $wagonsMap->get($wagonNumber);
                if (! $wagon) {
                    $wagon = Wagon::firstOrCreate(['number' => $wagonNumber]);
                    if (!empty($wagonLength) && $wagon->length != $wagonLength) {
                        $wagon->length = $wagonLength;
                        $wagon->save();
                    }
                    $wagonsMap->put($wagonNumber, $wagon);
                }
            }

            $wagonTrack = $tracksMap->get($exhibitionTrack) ?? WagonTrack::whereNumber($exhibitionTrack)->first();
            if(!$wagonTrack) {
                $errors[] = "Строка {$key}: выставочный путь '{$exhibitionTrack}' не найден.";
                continue;
            }

            $destination = $tracksMap->get($destinationTrack) ?? WagonTrack::whereNumber($destinationTrack)->first();
            if (!$destination) {
                $errors[] = "Строка {$key}: путь назначения '{$destinationTrack}' не найден.";
                continue;
            }

            if(!WagonStock::where(['wagon_id' => $wagon->id])->exists()) {
                $wagonStock = WagonStock::create([
                    'wagon_id' => $wagon->id, 'wagon_track_id' => $wagonTrack->id, 'wagon_track_destination_id' => $destination->id,
                    'wagon_task_id' => $wagonTask->id, 'reception_datetime' => $receptionDatetime, 'ordinal_number' => $ordinalNumber,
                    'country_code' => $countryCode, 'owner_name' => $ownerName, 'cargo_name' => $cargoName, 'state' => $state,
                    'operation_type' => $this->operationArr[$operationType], 'user_id' => $user->id, 'consignee_name' => $consigneeName
                ]);
                $createdCount++;
            } else {
                $wagonStock = WagonStock::where(['wagon_id' => $wagon->id, 'wagon_task_id' => $wagonTask->id])->first();
            }

            $ws = $wagonStock->attributesToArray();
            $ws['wagon_number'] = $wagon->number;
            $ws['wagon_task_number'] = $wagonTask->number;

            // Зафиксируем в таблице wagon_task_items
            if(WagonTaskItem::where(['wagon_id' => $wagon->id, 'wagon_task_id' => $wagonTask->id])->exists()) {
                $wagonTaskItem = WagonTaskItem::where(['wagon_id' => $wagon->id, 'wagon_task_id' => $wagonTask->id])->first();
            } else {
                if($wagonNumber != '*') {
                    $wagonTaskItem = WagonTaskItem::create($ws);
                }
            }

            if(!empty($containerNumber)) {
                $container = Container::firstOrCreate(['number' => $containerNumber]);
                $container->container_type = $containerType;
                $container->save();

                WagonStockContainer::create([
                    'wagon_stock_id' => $wagonStock->id, 'container_id' => $container->id, 'consignee_name' => $consigneeName,
                    'cargo_name' => $cargoName, 'container_number' => $container->number
                ]);

                WagonTaskItemContainer::create([
                    'wagon_task_item_id' => $wagonTaskItem->id, 'container_id' => $container->id, 'container_number' => $container->number,
                    'consignee_name' => $consigneeName, 'cargo_name' => $cargoName
                ]);

                $ws['container_id'] = $container->id;
                $ws['container_number'] = $containerNumber;
            } else {
                $wagonStock->consignee_name = $consigneeName;
                $wagonStock->save();
            }

            $ws['address_from'] = $wagonTrack->number;
            $ws['address_to'] = $destination->number;

            // Зафиксируем в логе
            WagonLog::create($ws);
        }

        return [
            'errors' => $errors,
            'created' => $createdCount,
        ];
    }

    public function createWagonTaskForCleaning($wagonTask, $sheetData, User $user): array
    {
        $errors = [];
        $updated = 0;

        foreach ($sheetData as $key=>$arr) {
            if ($key == 1) continue;
            $wagonNumber = $arr['A'];
            $destinationStation = $arr['B'];
            $direction = $arr['C'];
            $shipperName = $arr['D'];

            if(strlen($wagonNumber) > 8) {
                $errors[] = "Проверьте шаблон. В колонке 'В' ожидаем номер вагона.";
                break;
            }

            if(empty($wagonNumber)) break;

            if(empty($destinationStation)) $errors[] = "В шаблоне не указано Станция назначение. Строка №$key";
            if(empty($direction)) $errors[] = "В шаблоне не указан направление. Строка №$key";
            if(empty($shipperName)) $errors[] = "В шаблоне не указан Наименование грузоотправителя. Строка №$key";

            $wagon = Wagon::whereNumber($wagonNumber)->first();

            if(!$wagon) {
                // нет данный вагон в справочнике. обработка исключение
                $errors[] = "Вагон №" . $wagonNumber . " нет в справочнике вагонов. Строка №$key";
                continue;
            }

            $wagonStock = WagonStock::where(['wagon_id' => $wagon->id])->first();
            if(!$wagonStock) {
                // вагон нет в стоке. Обработка исключение
                $errors[] = "Вагон №" . $wagonNumber . " нет в стоке. Строка №$key";
            }
        }

        if(count($errors) > 0) {
            // если есть ошибок
            return [
                'errors' => $errors,
            ];
        } else {
            foreach ($sheetData as $key=>$arr) {
                if ($key == 1) continue;
                $wagonNumber = $arr['A'];
                $destinationStation = $arr['B'];
                $direction = $arr['C'];
                $shipperName = $arr['D'];

                if(is_null($wagonNumber)) break;

                $wagon = Wagon::whereNumber($wagonNumber)->first();
                $wagonStock = WagonStock::where(['wagon_id' => $wagon->id])->first();
                $wagonTrack = $wagonStock->wagon_track;

                $wagonStock->update([
                    'user_id' => $user->id,
                    'wagon_task_id' => $wagonTask->id,
                    'destination_station' => $destinationStation,
                    'direction' => $direction,
                    'shipper_name' => $shipperName,
                    'status' => 'cleaning_request'
                ]);

                $wagonStock->refresh();

                $wagonStockContainers = $wagonStock->containers;

                $ws = $wagonStock->attributesToArray();
                $ws['wagon_number'] = $wagonNumber;
                $ws['wagon_task_number'] = $wagonTask->number;
                $ws['address_from'] = $wagonTrack->number;
                $ws['address_to'] = $wagonTrack->number;

                // Зафиксируем в таблице wagon_task_items
                if(WagonTaskItem::where(['wagon_id' => $wagon->id, 'wagon_task_id' => $wagonTask->id])->exists()) {
                    $wagonTaskItem = WagonTaskItem::where(['wagon_id' => $wagon->id, 'wagon_task_id' => $wagonTask->id])->first();
                } else {
                    $wagonTaskItem = WagonTaskItem::create($ws);
                }

                if(count($wagonStockContainers) > 0) {
                    foreach($wagonStockContainers as $wagonStockContainer) {
                        $ws['container_id'] = $wagonStockContainer->container_id;
                        $ws['container_number'] = $wagonStockContainer->container_number;

                        WagonTaskItemContainer::create([
                            'wagon_task_item_id' => $wagonTaskItem->id, 'container_id' => $wagonStockContainer->container_id, 'container_number' => $wagonStockContainer->container_number,
                            'shipper_name' => $shipperName
                        ]);
                    }
                }

                // Зафиксируем в логе
                WagonLog::create($ws);
                $updated++;
            }

            return ['errors' => [], 'updated' => $updated];
        }
    }

    public function createWagonTaskForShunting($wagonTask, $sheetData, User $user): array
    {
        $errors = [];
        $updated = 0;

        $destTracks = collect($sheetData)->pluck('B')->filter()->unique()->values()->toArray();
        $destMap = WagonTrack::whereIn('number', $destTracks)->get()->keyBy('number');

        foreach ($sheetData as $key=>$arr) {
            if ($key == 1) continue;
            $wagonNumber = $arr['A'];
            $destinationTrack = $arr['B'];
            $consigneeName = $arr['C'];
            $operationType = $arr['D'];

            $wagon = Wagon::where('number', $wagonNumber)->first();
            if(!$wagon) {
                $errors[] = "Строка {$key}: вагон {$wagonNumber} не найден.";
                continue;
            }

            $wagonStock = WagonStock::where(['wagon_id' => $wagon->id])->first();
            if(!$wagonStock) {
                $errors[] = "Строка {$key}: вагон {$wagonNumber} не найден в стоке.";
                continue;
            }

            $wagonTrack = $wagonStock->wagon_track;

            $destinationWagonTrack = $destMap->get($destinationTrack) ?? WagonTrack::whereNumber($destinationTrack)->first();
            if (!$destinationWagonTrack) {
                $errors[] = "Строка {$key}: трек назначения '{$destinationTrack}' не найден.";
                continue;
            }

            $wagonStock->update([
                'wagon_track_destination_id' => $destinationWagonTrack->id,
                'consignee_name' => $consigneeName,
                'operation_type' => $this->operationArr[$operationType],
                'status' => 'moving_request',
                'wagon_task_id' => $wagonTask->id
            ]);

            $wagonStock->refresh();

            $ws = $wagonStock->attributesToArray();
            $ws['wagon_number'] = $wagon->number;
            $ws['wagon_task_number'] = $wagonTask->number;

            // Зафиксируем в таблице wagon_task_items
            WagonTaskItem::create($ws);

            $ws['address_from'] = $wagonTrack->number;
            $ws['address_to'] = $destinationWagonTrack->number;

            // Зафиксируем в логе
            WagonLog::create($ws);
            $updated++;
        }

        return ['errors' => $errors, 'updated' => $updated];
    }

    private function findPreviousWagonNumber($sheetData, $key)
    {
        for ($i = $key - 1; $i >= 0; $i--) {
            if (!empty($sheetData[$i]['C']) && $sheetData[$i]['C'] !== '*') {
                return $sheetData[$i]['C'];
            }
        }
        return null;
    }

    public function saveToExcel($wagonStocks): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Сток по вагонам');

        $str = "Сток по вагоном на: " . date('d.m.Y H:i');
        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A1', $str);
        $sheet->getStyle("A1")->getFont()->setSize(12)->setBold(true);

        // Вставляем авто размер для колонок
        $this->setAutoSizeColumn($sheet, true, 'A', 'B', 'C', 'D', 'E', 'F', 'G');

        $row_start = 3;
        $current_row = $row_start;
        foreach($wagonStocks as $trackNumber => $wagons){
            $sheet->mergeCells("A".$current_row.":G".$current_row);
            $sheet->setCellValue('A'.$current_row, "Путь " . $trackNumber . " (" . count($wagons) . " шт.)");
            $sheet->getStyle("A".$current_row)->getFont()->setSize(12)->setBold(true);

            $current_row += 2;
            $start = $current_row;

            $sheet->setCellValue('A'.$current_row, '№п/п');
            $sheet->setCellValue('B'.$current_row, 'Вагон');
            $sheet->setCellValue('C'.$current_row, 'Состояние');
            $sheet->setCellValue('D'.$current_row, 'Контейнер');
            $sheet->setCellValue('E'.$current_row, 'Груз.получатель');
            $sheet->setCellValue('F'.$current_row, 'Статус');
            $sheet->setCellValue('G'.$current_row, 'Дата подачи');
            $current_row++;

            foreach ($wagons as $wagon) {
                $last_row = $current_row;
                $sheet->setCellValue('A'.$current_row, $wagon->ordinal_number);
                $sheet->setCellValue('B'.$current_row, $wagon->wagon->number);
                $sheet->setCellValue('C'.$current_row, $wagon->state);
                $sheet->setCellValue('D'.$current_row, $wagon->wagon_containers_number);
                $sheet->setCellValue('E'.$current_row, $wagon->consignee_name);
                $sheet->setCellValue('F'.$current_row, $wagon->wagon_state_label);
                $sheet->setCellValue('G'.$current_row, $wagon->reception_datetime->format('d.m.Y H:i:s'));
                $current_row++;
            }

            $sheet->getStyle('A'.$start.':G'.$last_row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('00000000'));

            $current_row += 1;
        }

        $this->setHorizontal($sheet, Alignment::HORIZONTAL_RIGHT, 'A'.$current_row);

        $writer = new Xlsx($spreadsheet);
        $filename = date('d.m.Y_H_i_s_')."wagon_stocks.xlsx";

        // Подготовка папок для сохранение картинки
        $dir = '/wagon_files/stocks/' . date('Y-m').'/';
        if(!File::isDirectory(public_path(). $dir)){
            File::makeDirectory(public_path(). $dir, 0777, true);
        }

        $path_to_file = $dir.$filename;
        $writer->save(public_path() . $path_to_file);

        return $path_to_file;
    }

    public function generateReport($data): string
    {
        $from_date = $data['from_date'];
        $to_date = $data['to_date'];

        // Прибытие
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Прибытие');

        $columns = ['A','B','C','D','E','F','G','H'];

        $this->settingsForExcelColumns($sheet, $columns);

        $sheet->getDefaultRowDimension()->setRowHeight(-1);

        $sheet->setCellValue('A1', 'Компания / грузополучатель');
        $sheet->setCellValue('B1', 'Дата, время подачи вагона от ст. Жетысу');
        $sheet->setCellValue('C1', '№ вагона');
        $sheet->setCellValue('D1', 'Номер ведомости');
        $sheet->setCellValue('E1', 'Вид операций вагона');
        $sheet->setCellValue('F1', 'Номер контейнера');
        $sheet->setCellValue('G1', 'Наименование груза');
        $sheet->setCellValue('H1', 'Собственник вагона');

        $wagonLogs = WagonLog::whereDate('created_at', '>=', $from_date)->whereDate('created_at', '<=', $to_date)
                ->where('status', 'submission')
                ->get();

        $row_start = 2;
        $current_row = $row_start;

        foreach($wagonLogs as $index=>$wagonLog){
            $sheet->setCellValue('A'.$current_row, $wagonLog->consignee_name);
            $sheet->setCellValue('B'.$current_row, $wagonLog->reception_datetime);

            if ($index > 0 && $wagonLog->wagon_number == $wagonLogs[$index-1]->wagon_number) {
                $sheet->setCellValue('C'.$current_row, '*');
            } else {
                $sheet->setCellValue('C'.$current_row, $wagonLog->wagon_number);
            }

            $sheet->setCellValue('E'.$current_row, 'выгрузка');
            $sheet->setCellValue('F'.$current_row, $wagonLog->container_number);
            $sheet->setCellValue('G'.$current_row, $wagonLog->cargo_name);
            $sheet->setCellValue('H'.$current_row, $wagonLog->owner_name);
            $current_row++;
        }

        $this->setHorizontal($sheet, Alignment::HORIZONTAL_RIGHT, 'A'.$current_row);
        $current_row--;
        $sheet->getStyle('A1:H'.$current_row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('00000000'));

        // Погрузка
        $spreadsheet->createSheet();
        $sheet = $spreadsheet->setActiveSheetIndex(1);
        $sheet->setTitle('Погрузка');

        $columns = ['A','B','C','D','E','F','G','H', 'I'];

        $this->settingsForExcelColumns($sheet, $columns);

        $sheet->getDefaultRowDimension()->setRowHeight(-1);

        $sheet->setCellValue('A1', 'Компания / грузополучатель');
        $sheet->setCellValue('B1', 'Дата, время подачи вагона от ст. Жетысу');
        $sheet->setCellValue('C1', 'Номера вагонов');
        $sheet->setCellValue('D1', 'Номер ведомости');
        $sheet->setCellValue('E1', 'Наименование груза');
        $sheet->setCellValue('F1', 'Дата,время закрытия ведомости/оформление');
        $sheet->setCellValue('G1', 'Номер контейнера');
        $sheet->setCellValue('H1', 'Дата, фактическое время подачи на выставочный путь на ст. Жетысу');
        $sheet->setCellValue('I1', 'Принадлежность вагона');

        $wagonLogs = WagonLog::whereDate('created_at', '>=', $from_date)->whereDate('created_at', '<=', $to_date)
            ->where('status', 'cleaning_confirmed')
            ->get();

        $row_start = 2;
        $current_row = $row_start;

        foreach($wagonLogs as $index=>$wagonLog){
            $sheet->setCellValue('A'.$current_row, $wagonLog->consignee_name);
            $sheet->setCellValue('B'.$current_row, $wagonLog->reception_datetime);

            if ($index > 0 && $wagonLog->wagon_number == $wagonLogs[$index-1]->wagon_number) {
                $sheet->setCellValue('C'.$current_row, '*');
            } else {
                $sheet->setCellValue('C'.$current_row, $wagonLog->wagon_number);
            }

            $sheet->setCellValue('E'.$current_row, $wagonLog->cargo_name);
            $sheet->setCellValue('G'.$current_row, $wagonLog->container_number);
            $current_row++;
        }

        $current_row--;
        $sheet->getStyle('A1:I'.$current_row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('00000000'));

        $writer = new Xlsx($spreadsheet);
        $filename = date('d.m.Y_H_i_s_')."wagon_reports.xlsx";

        // Подготовка папок для сохранение картинки
        $dir = '/wagon_files/reports/' . date('Y-m').'/';
        if(!File::isDirectory(public_path(). $dir)){
            File::makeDirectory(public_path(). $dir, 0777, true);
        }

        $path_to_file = $dir.$filename;
        $writer->save(public_path() . $path_to_file);

        return $path_to_file;
    }

    private function settingsForExcelColumns(Worksheet $sheet, array $columns) :void
    {
        foreach ($columns as $col) {
            $sheet->getStyle("{$col}1")->getFont()->setSize(12)->setBold(true);

            $sheet->getColumnDimension($col)->setWidth(15);

            $sheet->getStyle($col)->getAlignment()->setWrapText(true);

            $sheet->getStyle($col)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->getStyle($col)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        }
    }
}
