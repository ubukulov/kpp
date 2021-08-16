<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Container;
use App\Models\ContainerAddress;
use App\Models\ContainerLog;
use App\Models\ContainerStock;
use App\Models\ContainerTask;
use App\Models\ImportLog;
use App\Models\Technique;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Str;
use File;
use Auth;

class ContainerController extends Controller
{
    public function getZones()
    {
        $zones = ContainerAddress::select(['zone', 'title'])->where('id', '>', 2)->orderBy('id', 'ASC')->groupBy('zone')->get();
        return response()->json($zones);
    }

    public function getContainers()
    {
        $containerIds = ContainerStock::where(['container_address_id' => 1, 'status' => 'incoming'])->select('container_id')->get();
        $containers = Container::whereIn('id', $containerIds)->get();
        return response()->json($containers);
    }

    public function getTechniques()
    {
        return response()->json(Technique::all());
    }

    public function getFreeRows(Request $request)
    {
        $zone = $request->input('zone');
        $container_id = $request->input('container_id');
        return response()->json(ContainerAddress::getFreeRows($zone, $container_id));
    }

    public function getFreePlaces(Request $request)
    {
        $zone = $request->input('zone');
        $row = $request->input('row');
        $container_id = $request->input('container_id');
        return response()->json(ContainerAddress::getFreePlaces($zone, $row, $container_id));
    }

    public function getFreeFloors(Request $request)
    {
        $zone = $request->input('zone');
        $row = $request->input('row');
        $place = $request->input('place');
        $container_id = $request->input('container_id');
        return response()->json(ContainerAddress::getFreeFloors($zone, $row, $place, $container_id));
    }

    public function receiveContainerChange(Request $request)
    {
        $data = $request->all();
        $name = $data['zone']."-".$data['row']."-".$data['place']."-".$data['floor'];
        $container_address = ContainerAddress::whereName($name)->first();
        if ($container_address) {
            $container = Container::find($data['container_id']);
            if ($container) {
                $container_stock = ContainerStock::where(['container_id' => $container->id, 'container_address_id' => 1])->first();
                if ($container_stock) {
                    $current_address_name = $container_stock->container_address->name;
                    $container_stock->container_address_id = $container_address->id;
                    $container_stock->status = 'received';
                    $container_stock->save();

                    // Зафиксируем в лог
                    ContainerLog::create([
                        'user_id' => Auth::id(), 'container_id' => $container->id, 'container_number' => $container->number,
                        'operation_type' => 'received', 'technique_id' => $data['technique_id'], 'address_from' => $current_address_name,
                        'address_to' => $container_address->name, 'state' => $container_stock->state
                    ]);

                    return response(['data' => '<span style="font-size: 30px;line-height: 30px;">Контейнер успешно размещен!!!</span>'], 200);
                } else {
                    return response(['data' => 'В таблице остатки не найдено запись на размещение'], 404);
                }
            } else {
                return response(['data' => 'Не найден контейнер'], 404);
            }
        } else {
            return response(['data' => 'Не найден адрес контейнера'], 404);
        }
    }

    public function movingContainerChange(Request $request)
    {
        $data = $request->all();
        $name = $data['zone']."-".$data['row']."-".$data['place']."-".$data['floor'];
        $container_address = ContainerAddress::whereName($name)->first();
        if ($container_address) {
            $container = Container::find($data['container_id']);
            if ($container) {
                $container_stock = ContainerStock::where(['container_id' => $container->id])->first();
                $current_address_name = $container_stock->container_address->name;
                if ($container_stock) {
                    $container_stock->container_address_id = $container_address->id;
                    $container_stock->status = 'received';
                    $container_stock->save();

                    // Зафиксируем в лог
                    ContainerLog::create([
                        'user_id' => Auth::id(), 'container_id' => $container->id, 'container_number' => $container->number,
                        'operation_type' => 'received', 'technique_id' => $data['technique_id'], 'address_from' => $current_address_name,
                        'address_to' => $container_address->name, 'state' => $container_stock->state
                    ]);

                    return response(['data' => '<span style="font-size: 30px;line-height: 30px;color: #fff;">Контейнер успешно перемещен!!!</span>'], 200);
                } else {
                    return response(['data' => 'В таблице остатки не найдено запись на размещение'], 404);
                }
            } else {
                return response(['data' => 'Не найден контейнер'], 404);
            }
        } else {
            return response(['data' => 'Не найден адрес контейнера'], 404);
        }
    }

    public function receiveContainerByOperator(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->except(['document_base', 'upload_file']);
            $data['user_id'] = Auth::id();
            $container_task = ContainerTask::create($data);
            // Подготовка папок для сохранение картинки
            $dir = '/task_files/'. substr(md5(microtime()), mt_rand(0, 30), 2);
            if(!File::isDirectory(public_path(). $dir)){
                File::makeDirectory(public_path(). $dir, 0777, true);
            }

            // Документ основание
            if ($request->input('document_base') != 'undefined'){
                $document_base = $request->file('document_base');
                $document_base->move(public_path() . $dir, $document_base->getClientOriginalName());
                $container_task->document_base = $dir.'/'.$document_base->getClientOriginalName();
                $container_task->save();
            }

            // Файл с контейнерами
            $container_ids = [];
            $container_states = [];
            $upload_file = $request->file('upload_file');
            $filename = substr(md5(time()), 0, 5) . "_" . $upload_file->getClientOriginalName();
            $path_to_file = $dir.'/'.$filename;
            $upload_file->move(public_path() . $dir, $filename);
            $container_task->upload_file = $path_to_file;
            $container_task->save();

            $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify(public_path($path_to_file));
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
            $spreadsheet = $reader->load(public_path($path_to_file));
            $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

            $user_id = Auth::id();
            foreach ($sheetData as $arr) {
                $number = trim(strtoupper($arr['A']));
                $state = (empty($arr['D'])) ? '' : $arr['D'];
                $data_import = [
                    'container_task_id' => $container_task->id, 'container_number' => $number,
                    'user_id' => $user_id, 'ip' => $_SERVER['REMOTE_ADDR']
                ];

                if(strlen($number) < 11) {
                    $data_import['status'] = 'not';
                    $data_import['comments'] = 'Номер контейнера не правильно.';
                    ImportLog::create($data_import);
                    continue;
                }

                if(strlen($number) > 11) {
                    $data_import['status'] = 'not';
                    $data_import['comments'] = 'Номер контейнера не правильно.';
                    ImportLog::create($data_import);
                    continue;
                }

                $str = substr($number,0,4);
                if (!preg_match('/^[A-Z]{4}$/', $str)) {
                    $data_import['status'] = 'not';
                    $data_import['comments'] = 'Номер контейнера должно начаться с 4-х букв латинского алфавита';
                    ImportLog::create($data_import);
                    continue;
                }

                $str = substr($number,4,7);
                if (!preg_match('/^[0-9]{7}$/', $str)) {
                    $data_import['status'] = 'not';
                    $data_import['comments'] = 'В номере контейнера должно быть 7 цифр';
                    ImportLog::create($data_import);
                    continue;
                }

                $container = Container::whereNumber($number)->first();
                if ($data['task_type'] == 'receive') {
                    // Размещение
                    if (!$container) {
                        $container_type = (empty($arr['C'])) ? '40' : $arr['C'];
                        $container_type = (string) $container_type;
                        $container = Container::create(['number' => $number, 'company' => $arr['B'], 'container_type' => $container_type]);
                    }
                } else {
                    // На отбор
                    if (!$container) {
                        $data_import['status'] = 'not';
                        $data_import['comments'] = 'Контейнер не найдено в справочнике';
                        ImportLog::create($data_import);
                        continue;
                    }
                }

                $container_ids[$container->id] = $number;
                $container_states[$container->id] = $state;

                // Записываем истории об импорте
                if ($data['task_type'] == 'receive') {
                    if (ContainerStock::exists($container->id)) {
                        $data_import['status'] = 'not';
                        $data_import['comments'] = 'Контейнер уже имеется в остатки';
                        ImportLog::create($data_import);
                    } else {
                        $data_import['status'] = 'ok';
                        $data_import['comments'] = 'Контейнер в порядке';
                        ImportLog::create($data_import);
                    }
                } else {
                    if (ContainerStock::is_shipping($container->id)) {
                        $data_import['status'] = 'ok';
                        $data_import['comments'] = 'Контейнер в порядке';
                        ImportLog::create($data_import);
                    } else {
                        $data_import['status'] = 'not';
                        $data_import['comments'] = 'Контейнер не в порядке';
                        ImportLog::create($data_import);
                    }
                }
            }

            if ($container_task->isSuccessImport()) {
                if ($data['task_type'] == 'receive') {
                    // добавляем в остатки
                    foreach ($container_ids as $container_id => $container_number) {
                        $container_stock = ContainerStock::create(['container_id' => $container_id, 'container_address_id' => 1, 'state' => $container_states[$container_id]]);
                        if ($container_stock) {
                            $container_address = ContainerAddress::findOrFail(1);
                            // Зафиксируем в лог
                            ContainerLog::create([
                                'user_id' => Auth::id(), 'container_id' => $container_id, 'container_number' => $container_number,
                                'operation_type' => 'incoming', 'address_from' => $container_task->trans_type,
                                'address_to' => $container_address->name, 'state' => $container_stock->state
                            ]);
                        }
                    }
                } else {
                    foreach ($container_ids as $container_id => $container_number) {
                        $container_stock = ContainerStock::where(['container_id' => $container_id])->first();
                        if ($container_stock) {
                            $container_stock->status = 'in_order';
                            $container_stock->save();

                            $address = $container_stock->container_address->name;
                            // Зафиксируем в лог
                            ContainerLog::create([
                                'user_id' => Auth::id(), 'container_id' => $container_id, 'container_number' => $container_number,
                                'operation_type' => 'in_order', 'address_from' => $address,
                                'address_to' => $address, 'state' => $container_stock->state
                            ]);
                        }
                    }
                }
            } else {
                $container_task->status = 'failed';
                $container_task->save();
            }

            $container_task->container_ids = json_encode($container_ids);
            $container_task->save();
            DB::commit();

            return response('Заявка успешно оформлен', 200);
        } catch (\Exception $exception) {
            DB::rollBack();
            return response('Ошибка с сервером', 500);
        }
    }

    public function getInfoForContainer(Request $request)
    {
        $container_number = $request->input('container_number');
        $container = Container::where('number', 'like', '%'.$container_number)->first();
        if ($container) {
            $container_stock = ContainerStock::where(['container_id' => $container->id])->first();
            if ($container_stock) {
                $container_address = $container_stock->container_address->name;
                if ($container_stock->container_address_id == 1) {
                    return response([
                        'data' => [
                            'text' => "Контейнер: <span style='color: red;'>".$container->number."<br> ($container->company, $container_stock->state, $container->container_type)</span> необходимо <span style='color: green;'>РАЗМЕСТИТЬ.</span>",
                            'event' => 1,
                            'container_id' => $container->id,
                            'container_number' => $container->number,
                            'current_container_address' => $container_address
                        ]
                    ], 200);
                }
                if($container_stock->container_address_id == 2 && $container_stock->status == 'shipped') {
                    return response("Контейнер №".$container->number." найдено. Контейнер находится в зоне отбора", 403);
                }
                if($container_stock->container_address_id > 2 && $container_stock->status == 'received') {
                    return response([
                        'data' => [
                            'text' => "Контейнер: <span style='color: red;'>".$container->number."<br> ($container->company, $container_stock->state, $container->container_type)</span>  <br>Адрес: $container_address",
                            'event' => 3,
                            'container_id' => $container->id,
                            'container_number' => $container->number,
                            'current_container_address' => $container_address
                        ]
                    ], 200);
                }
                if($container_stock->container_address_id > 2 && $container_stock->status == 'in_order') {
                    return response([
                        'data' => [
                            'text' => "Контейнер: <span style='color: red;'>".$container->number."<br> ($container->company, $container_stock->state, $container->container_type)</span>  <br>Адрес: $container_address",
                            'event' => 2,
                            'container_id' => $container->id,
                            'container_number' => $container->number,
                            'current_container_address' => $container_address
                        ]
                    ], 200);
                }
            } else {
                return response("Контейнер №".$container->number." не найдено в стоке", 404);
            }
        } else {
            return response("Контейнер №".$container_number." не найден", 404);
        }
    }

    public function checkingTheContainerForMovement(Request $request)
    {
        $container_id = $request->input('container_id');
        $container_stock = ContainerStock::where(['container_id' => $container_id])->first();
        $container_address = $container_stock->container_address;
        $name = $container_address->name;
        $arr = explode("-", $name);
        $floor = $container_address->floor;

        if ($floor == 4) {
            return response('Можно перемещать контейнера', 200);
        } else {
            $containers_numbers = [];
            $caIds = [];
            while ($floor < 4) {
                $floor++;
                $arr[3] = $floor;
                $n = implode("-", $arr);
                $caIds[] = $n;
            }

            $ca = ContainerAddress::whereIn('name', $caIds)->get();
            foreach ($ca->reverse() as $item) {
                if ($cs = ContainerStock::checking_container_by_address($item->id)) {
                    $containers_numbers[] = $cs->container->number;
                }
            }

            if (empty($containers_numbers)) {
                return response('Можно перемещать контейнера', 200);
            } else {
                $numbers = implode("<br>", $containers_numbers);
                return response([
                    'data' => [
                        'text' => "<span style='color: darkorange;'>Сначала необходимо переместить контейнера: <br>$numbers</span>"
                    ]
                ], 403);
            }

            /*
            $arr[3] = $floor + 1;
            $n = implode("-", $arr);
            $ca = ContainerAddress::whereName($n)->first();
            if (ContainerStock::checking_container_by_address($ca->id)) {
                return response([
                    'data' => [
                        'text' => "<span style='color: red;'>Сначала необходимо переместить контейнера: $floor</span>"
                    ]
                ], 403);
            } else {
                return response('Можно перемещать контейнера', 200);
            }*/

        }
    }

    public function updateContainerTask(Request $request, $container_task_id)
    {
        DB::beginTransaction();
        try {
            $data = $request->except(['document_base', 'upload_file']);
            $container_task = ContainerTask::findOrFail($container_task_id);
            $container_task->update($data);
            // Подготовка папок для сохранение картинки
            $dir = '/task_files/'. substr(md5(microtime()), mt_rand(0, 30), 2);
            if(!File::isDirectory(public_path(). $dir)){
                File::makeDirectory(public_path(). $dir, 0777, true);
            }

            // Документ основание
            if ($request->hasFile('document_base')){
                if(!empty($container_task->document_base) && file_exists(public_path($container_task->document_base))) {
                    unlink(public_path($container_task->document_base));
                }
                $document_base = $request->file('document_base');
                $document_base->move(public_path() . $dir, $document_base->getClientOriginalName());
                $container_task->document_base = $dir.'/'.$document_base->getClientOriginalName();
                $container_task->save();
            }

            // Файл с контейнерами
            $container_ids = [];
            $container_states = [];
            if ($request->hasFile('upload_file')) {
                if(!empty($container_task->upload_file) && file_exists(public_path($container_task->upload_file))) {
                    unlink(public_path($container_task->upload_file));
                }
                $upload_file = $request->file('upload_file');
                $filename = substr(md5(time()), 0, 5) . "_" . $upload_file->getClientOriginalName();
                $path_to_file = $dir.'/'.$filename;
                $upload_file->move(public_path() . $dir, $filename);
                $container_task->upload_file = $path_to_file;
                $container_task->save();

                $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify(public_path($path_to_file));
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
                $spreadsheet = $reader->load(public_path($path_to_file));
                $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

                $user_id = Auth::id();
                $container_task->deleteImportLogs();
                foreach ($sheetData as $arr) {
                    $number = trim(strtoupper($arr['A']));
                    $state = (empty($arr['D'])) ? '' : $arr['D'];
                    $data_import = [
                        'container_task_id' => $container_task->id, 'container_number' => $number,
                        'user_id' => $user_id, 'ip' => $_SERVER['REMOTE_ADDR']
                    ];

                    if(strlen($number) < 11) {
                        $data_import['status'] = 'not';
                        $data_import['comments'] = 'Номер контейнера не правильно.';
                        ImportLog::create($data_import);
                        continue;
                    }

                    if(strlen($number) > 11) {
                        $data_import['status'] = 'not';
                        $data_import['comments'] = 'Номер контейнера не правильно.';
                        ImportLog::create($data_import);
                        continue;
                    }

                    $str = substr($number,0,4);
                    if (!preg_match('/^[A-Z]{4}$/', $str)) {
                        $data_import['status'] = 'not';
                        $data_import['comments'] = 'Номер контейнера должно начаться с 4-х букв латинского алфавита';
                        ImportLog::create($data_import);
                        continue;
                    }

                    $str = substr($number,4,7);
                    if (!preg_match('/^[0-9]{7}$/', $str)) {
                        $data_import['status'] = 'not';
                        $data_import['comments'] = 'В номере контейнера должно быть 7 цифр';
                        ImportLog::create($data_import);
                        continue;
                    }

                    $container = Container::whereNumber($number)->first();
                    if ($data['task_type'] == 'receive') {
                        // Размещение
                        if (!$container) {
                            $container = Container::create(['number' => $number, 'company' => $arr['B']]);
                        }
                    } else {
                        // На отбор
                        if (!$container) {
                            $data_import['status'] = 'not';
                            $data_import['comments'] = 'Контейнер не найдено в справочнике';
                            ImportLog::create($data_import);
                            continue;
                        }
                    }

                    $container_ids[$container->id] = $number;
                    $container_states[$container->id] = $state;

                    // Записываем истории об импорте
                    if ($data['task_type'] == 'receive') {
                        if (ContainerStock::exists($container->id)) {
                            $data_import['status'] = 'not';
                            $data_import['comments'] = 'Контейнер уже имеется в остатки';
                            ImportLog::create($data_import);
                        } else {
                            $data_import['status'] = 'ok';
                            $data_import['comments'] = 'Контейнер в порядке';
                            ImportLog::create($data_import);
                        }
                    } else {
                        if (ContainerStock::is_shipping($container->id)) {
                            $data_import['status'] = 'ok';
                            $data_import['comments'] = 'Контейнер в порядке';
                            ImportLog::create($data_import);
                        } else {
                            $data_import['status'] = 'not';
                            $data_import['comments'] = 'Контейнер не в порядке';
                            ImportLog::create($data_import);
                        }
                    }
                }
            }

            if ($container_task->isSuccessImport()) {
                if ($data['task_type'] == 'receive') {
                    // добавляем в остатки
                    foreach ($container_ids as $container_id => $container_number) {
                        $container_stock = ContainerStock::where(['container_id' => $container_id, 'container_address_id' => 1])->first();
                        if (!$container_stock) {
                            $container_stock = ContainerStock::create(['container_id' => $container_id, 'container_address_id' => 1, 'state' => $container_states[$container_id]]);
                            $container_address = ContainerAddress::findOrFail(1);
                            // Зафиксируем в лог

                            ContainerLog::create([
                                'user_id' => Auth::id(), 'container_id' => $container_id, 'container_number' => $container_number,
                                'operation_type' => 'incoming', 'address_from' => $container_task->trans_type,
                                'address_to' => $container_address->name, 'state' => $container_stock->state
                            ]);
                        }
                    }
                } else {
                    foreach ($container_ids as $container_id => $container_number) {
                        $container_stock = ContainerStock::where(['container_id' => $container_id])->first();
                        if ($container_stock) {
                            $container_stock->status = 'in_order';
                            $container_stock->save();

                            $address = $container_stock->container_address->name;
                            // Зафиксируем в лог
                            ContainerLog::create([
                                'user_id' => Auth::id(), 'container_id' => $container_id, 'container_number' => $container_number,
                                'operation_type' => 'in_order', 'address_from' => $address,
                                'address_to' => $address, 'state' => $container_stock->state
                            ]);
                        }
                    }
                }
                $container_task->status = 'open';
                $container_task->save();
            } else {
                $container_task->status = 'failed';
                $container_task->save();
            }

            $container_task->container_ids = json_encode($container_ids);
            $container_task->save();
            DB::commit();

            return redirect()->route('kt.kt_operator');
        } catch (\Exception $exception) {
            DB::rollBack();
        }
    }

    public function checkingTheContainerForDispensing(Request $request)
    {
        $container_id = $request->input('container_id');
        $container_stock = ContainerStock::where(['container_id' => $container_id, 'status' => 'in_order'])->first();
        if ($container_stock) {
            $container_address = $container_stock->container_address;
            $name = $container_address->name;
            $arr = explode("-", $name);
            $floor = $container_address->floor;

            if ($floor == 4) {
                return response('Контейнер готов к отбору', 200);
            } else {
                $containers_numbers = [];
                $caIds = [];
                while ($floor < 4) {
                    $floor++;
                    $arr[3] = $floor;
                    $n = implode("-", $arr);
                    $caIds[] = $n;
                }

                $ca = ContainerAddress::whereIn('name', $caIds)->get();
                foreach ($ca->reverse() as $item) {
                    if ($cs = ContainerStock::checking_container_by_address($item->id)) {
                        $containers_numbers[] = $cs->container->number;
                    }
                }

                if (empty($containers_numbers)) {
                    return response('Контейнер готов к отбору', 200);
                } else {
                    $numbers = implode("<br>", $containers_numbers);
                    return response([
                        'data' => [
                            'text' => "<span style='color: darkorange;'>Сначала необходимо переместить контейнера: <br>$numbers</span>"
                        ]
                    ], 403);
                }
                //**********************************
                /*$arr[3] = $floor + 1;
                $n = implode("-", $arr);
                $ca = ContainerAddress::whereName($n)->first();
                if (ContainerStock::checking_container_by_address($ca->id)) {
                    return response([
                        'data' => [
                            'text' => 'Для отбора данного контейнера нужно перемещать других контейнеров по выше'
                        ]
                    ], 403);
                } else {
                    return response('Контейнер готов к отбору', 200);
                }*/
            }
        } else {
            return response([
                'data' => [
                    'text' => '<span style="color: red; font-size: 30px;">Отсутствует заявка на выдачу</span>'
                ]
            ], 403);
        }
    }

    public function shippingContainerChange(Request $request)
    {
        $container_id = $request->input('container_id');
        $technique_id = $request->input('technique_id');
        $container = Container::findOrFail($container_id);
        if ($container) {
            $container_stock = ContainerStock::where(['container_id' => $container->id, 'status' => 'in_order'])->first();
            $current_address_name = $container_stock->container_address->name;
            $to_address_name = ContainerAddress::findOrFail(2);
            if ($container_stock) {
                $container_stock->container_address_id = $to_address_name->id;
                $container_stock->status = 'shipped';
                $container_stock->save();

                // Зафиксируем в лог
                ContainerLog::create([
                    'user_id' => Auth::id(), 'container_id' => $container->id, 'container_number' => $container->number,
                    'operation_type' => 'shipped', 'technique_id' => $technique_id, 'address_from' => $current_address_name,
                    'address_to' => $to_address_name->name, 'state' => $container_stock->state
                ]);

                return response(['data' => '<span style="font-size: 30px;line-height: 30px;color: #fff;">Контейнер успешно выдан!!!</span>'], 200);
            } else {
                return response(['data' => 'В таблице остатки не найдено запись на отбор'], 404);
            }
        } else {
            return response(['data' => 'Не найден контейнер'], 404);
        }
    }
}
