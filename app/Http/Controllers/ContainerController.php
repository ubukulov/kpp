<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\Container;
use App\Models\ContainerAddress;
use App\Models\ContainerLog;
use App\Models\ContainerStock;
use App\Models\ContainerTask;
use App\Models\ImportLog;
use App\Models\Session;
use App\Models\Technique;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Str;
use File;
use Auth;

class ContainerController extends BaseController
{
    public function getZones()
    {
        $zones = ContainerAddress::select(['zone', 'title'])
            ->whereIn('kind', ['r', 'k', 'pole', 'cia', 'rich', 'buffer'])
            ->orderBy('title', 'ASC')
            ->groupBy('zone')->get();
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
        $techniques = Technique::/*whereNotIn('id', Session::getTechniqueIds())->*/orderBy('name', 'ASC')->get();
        return response()->json($techniques);
    }

    public function getFreeRows(Request $request)
    {
        return response()->json(ContainerAddress::getFreeRows($request->all()));
    }

    public function getFreePlaces(Request $request)
    {
        return response()->json(ContainerAddress::getFreePlaces($request->all()));
    }

    public function getFreeFloors(Request $request)
    {
        return response()->json(ContainerAddress::getFreeFloors($request->all()));
    }

    public function getListRows(Request $request)
    {
        $zone = $request->input('zone');
        $container_address = ContainerAddress::whereZone($zone)
            ->select('row')
            ->leftJoin('container_stocks', 'container_stocks.container_address_id', '=', 'container_address.id')
            ->leftJoin('containers', 'containers.id', '=', 'container_stocks.container_id')
            ->get();

        return response()->json($container_address);
    }

    public function getListContainersInRow(Request $request)
    {
        $zone = $request->input('zone');
        $row  = $request->input('row');
        $container_address = ContainerAddress::whereZone($zone)
            ->leftJoin('container_stocks', 'container_stocks.container_address_id', '=', 'container_address.id')
            ->leftJoin('containers', 'containers.id', '=', 'container_stocks.container_id')
            ->whereRow($row)
            ->get();

        return response()->json($container_address);
    }

    /**
     * Метод для размещения (завоз) контейнера в зоне.
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function receiveContainerChange(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();
            $name = $data['zone']."-".$data['row']."-".$data['place']."-".$data['floor'];

            $container_address = ContainerAddress::whereName($name)->first();
            if ($container_address) {
                $container = Container::findOrFail($data['container_id']);
                if ($container) {
                    $container_address_dmu_in = ContainerAddress::whereName('damu_in')->first();
                    if ($container_address_dmu_in) {
                        $container_stock = ContainerStock::where(['container_id' => $container->id, 'container_address_id' => $container_address_dmu_in->id])->first();
                        if ($container_stock) {
                            $current_address_name = $container_address_dmu_in->name;
                            $container_stock->container_address_id = $container_address->id;
                            $container_stock->status = 'received';
                            $container_stock->save();

                            $cs = $container_stock->attributesToArray();
                            $cs['start_date'] = date('Y-m-d H:i:s', $data['start_date'] / 1000);
                            $cs['user_id'] = Auth::id();
                            $cs['container_number'] = $container->number;
                            $cs['operation_type'] = 'received';
                            $cs['technique_id'] = $data['technique_id'];
                            $cs['address_from'] = $current_address_name;
                            $cs['address_to'] = $container_address->name;
                            $cs['action_type'] = 'put';
                            $cs['slinger_ids'] = $data['slinger_ids'];

                            // Зафиксируем в лог
                            ContainerLog::create($cs);

                            // Зафиксируем в таблицу import_logs
                            $container_task = $container_stock->container_task;
                            $import_log = ImportLog::where(['container_task_id' => $container_task->id, 'container_number' => $container->number])->first();
                            if ($import_log) {
                                $import_log->state = 'posted';
                                $import_log->save();
                            }

                            // Если этот контейнер последняя в заявке, то автоматический закрываем заявку
                            if (!is_null($container_stock->container_task_id)) {
                                $container_task = $container_stock->container_task;
                                if ($container_task && $container_task->allowCloseThisTask()) {
                                    ContainerTask::complete($container_task->id);
                                }
                            }
                            DB::commit();
                            return response(['data' => '<span style="font-size: 30px;line-height: 30px;">Контейнер успешно размещен!!!</span>'], 200);
                        } else {
                            return response(['data' => 'В таблице остатки не найдено запись на размещение'], 404);
                        }
                    } else {
                        return response(['data' => 'Не найден адрес damu_in'], 404);
                    }
                } else {
                    return response(['data' => 'Не найден контейнер'], 404);
                }
            } else {
                return response(['data' => 'Не найден адрес контейнера'], 404);
            }
        }catch (\Exception $exception){
            DB::rollBack();
            echo $exception->getMessage();
            exit();
        }
    }

    public function movingContainerChange(Request $request)
    {
        DB::beginTransaction();
        try {
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
                        $container_stock->save();

                        $cs = $container_stock->attributesToArray();
                        $cs['start_date'] = date('Y-m-d H:i:s', $data['start_date'] / 1000);
                        $cs['user_id'] = Auth::id();
                        $cs['container_number'] = $container->number;
                        $cs['operation_type'] = 'received';
                        $cs['technique_id'] = $data['technique_id'];
                        $cs['address_from'] = $current_address_name;
                        $cs['address_to'] = $container_address->name;
                        $cs['action_type'] = 'move';
                        $cs['slinger_ids'] = $data['slinger_ids'];

                        // Зафиксируем в лог
                        ContainerLog::create($cs);
                        DB::commit();
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
        } catch (\Exception $exception) {
            DB::rollBack();
            echo $exception->getMessage();
            exit();
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
            $container_dataset = [];
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
                if ($this->isRussian($arr['A'])) {
                    continue;
                }
				if (empty($arr['A'])) {
					continue;
				}
                if ($container_task->task_type == 'receive') {
                    $number = trim(strtoupper($arr['A']));
                    $company = trim(strtoupper($arr['B']));
                    $container_type = (empty($arr['C'])) ? '40' : (int) $arr['C'];
                    $state = (empty($arr['D'])) ? '' : $arr['D'];
                    $custom = (mb_strtolower($arr['E']) == 'да') ? 'yes' : 'not';
                    $car_number_carriage = (isset($arr['F'])) ? $arr['F'] : null;
                    $datetime_submission = (isset($arr['G'])) ? $arr['G'] : null;
                    $datetime_arrival = (isset($arr['H'])) ? $arr['H'] : null;
                    $contractor = (isset($arr['K'])) ? $arr['K'] : null;
                    $note = (isset($arr['L'])) ? $arr['L'] : null;
                    $seal_number_document = (isset($arr['I'])) ? $arr['I'] : null;
                    $seal_number_fact = (isset($arr['J'])) ? $arr['J'] : null;

                    if(is_null($datetime_submission) OR is_null($datetime_arrival)){
                        continue;
                    }
                } else {
                    $number = trim(strtoupper($arr['A']));
                    $company = trim(strtoupper($arr['B']));
                    $car_number_carriage = (isset($arr['C'])) ? $arr['C'] : null;
                    $seal_number_fact = (isset($arr['D'])) ? $arr['D'] : null;
                    $fio_driver = (isset($arr['E'])) ? $arr['E'] : null;
                    $contractor = (isset($arr['F'])) ? $arr['F'] : null;
                    $note = (isset($arr['G'])) ? $arr['G'] : null;
                    $seal_number_document = null;
                    $datetime_submission = null;
                    $datetime_arrival = null;
                    $custom = null;
                    $state = '';
                    $container_type = '40';
                }

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
                        $container = Container::create(['number' => $number, 'company' => $company, 'container_type' => (string)$container_type]);
                    }
                } else {
                    // На отбор
                    if (!$container) {
                        $data_import['status'] = 'not';
                        $data_import['comments'] = 'Контейнер отсутствует в остатке';
                        ImportLog::create($data_import);
                        continue;
                    }
                }

                $container_ids[$container->id] = $number;

                $container_dataset[$container->id] = [
                    'state' => $state, 'customs' => $custom, 'car_number_carriage' => $car_number_carriage, 'datetime_submission' => $datetime_submission,
                    'datetime_arrival' => $datetime_arrival, 'contractor' => $contractor, 'note' => $note, 'seal_number_document' => $seal_number_document,
                    'seal_number_fact' => $seal_number_fact, 'container_id' => $container->id, 'container_number' => $number, 'company' => $company,
                    'container_type' => $container->container_type
                ];

                // Записываем истории об импорте
                if ($data['task_type'] == 'receive') {
                    if (ContainerStock::exists($container->id)) {
                        $data_import['status'] = 'not';
                        $data_import['comments'] = 'Контейнер уже имеется в остатки';
                        ImportLog::create($data_import);
                    } else {
                        $data_import['status'] = 'ok';
                        $data_import['comments'] = 'Успешно';
                        ImportLog::create($data_import);
                    }
                } else {
                    if (ContainerStock::is_shipping($container->id)) {
                        $data_import['status'] = 'ok';
                        $data_import['comments'] = 'Успешно';
                        ImportLog::create($data_import);
                    } else {
                        $data_import['status'] = 'not';
                        $data_import['comments'] = 'Контейнер отсутствует в остатке';
                        ImportLog::create($data_import);
                    }
                }
            }

            if ($container_task->isSuccessImport()) {
                if ($data['task_type'] == 'receive') {
                    // добавляем в остатки
                    $container_address_dmu_in = ContainerAddress::whereName('damu_in')->first();
                    $containers_sam = [];
                    foreach ($container_ids as $container_id => $container_number) {
                        $container_stock = ContainerStock::where(['container_id' => $container_id, 'container_address_id' => 1385])->first();
                        $dataset = $container_dataset[$container_id];
                        $dataset['container_task_id'] = $container_task->id;
                        $dataset['status'] = 'incoming';
                        $dataset['container_address_id'] = $container_address_dmu_in->id;
                        if ($container_stock) {
                            $container_stock->container_task_id = $container_task->id;
                            $container_stock->status = 'incoming';
                            $container_stock->container_address_id = $container_address_dmu_in->id;
                            $container_stock->save();
                        } else {
                            $container_stock = ContainerStock::create($dataset);
                            if($dataset['company'] == 'SAMSUNG') {
                                $containers_sam[$dataset['container_number']]['task_number'] = $container_task->getNumber();
                                $containers_sam[$dataset['container_number']]['number'] = $dataset['container_number'];
                                $containers_sam[$dataset['container_number']]['date']   = $container_stock->created_at;
                                $containers_sam[$dataset['container_number']]['container_type']   = $dataset['container_type'];
                                $containers_sam[$dataset['container_number']]['state']   = $dataset['state'];
                                $containers_sam[$dataset['container_number']]['car_number_carriage']   = $dataset['car_number_carriage'];
                                $containers_sam[$dataset['container_number']]['datetime_submission']   = $dataset['datetime_submission'];
                                $containers_sam[$dataset['container_number']]['datetime_arrival']   = $dataset['datetime_arrival'];
                                $containers_sam[$dataset['container_number']]['seal_number_document']   = $dataset['seal_number_document'];
                                $containers_sam[$dataset['container_number']]['seal_number_fact']   = $dataset['seal_number_fact'];
                                $containers_sam[$dataset['container_number']]['note']   = $dataset['note'];
                            }
                        }

                        if ($container_stock) {
                            $cs['start_date'] = Carbon::now();
                            $dataset['user_id'] = Auth::id();
                            $dataset['operation_type'] = 'incoming';
                            $dataset['address_from'] = '';
                            $dataset['address_to'] = $container_address_dmu_in->name;
                            $dataset['action_type'] = 'reception';
                            // Зафиксируем в лог
                            ContainerLog::create($dataset);
                        }
                    }

                    // Отправка сообщение о том что пришло контейнер
                    if(!empty($containers_sam) && $container_task->trans_type == 'train') {
                        $this->sendMessageSam($containers_sam);
                    }
                } else {
                    foreach ($container_ids as $container_id => $container_number) {
                        $container_stock = ContainerStock::where(['container_id' => $container_id])->first();
                        if ($container_stock) {
                            $container_stock->container_task_id = $container_task->id;
                            $container_stock->status = 'in_order';
                            $container_stock->save();

                            $address = $container_stock->container_address->name;
                            $cs = $container_stock->attributesToArray();
                            $cs['start_date'] = Carbon::now();
                            $cs['user_id'] = Auth::id();
                            $cs['container_task_id'] = $container_task->id;
                            $cs['container_number'] = $container_number;
                            $cs['operation_type'] = 'in_order';
                            $cs['address_from'] = $address;
                            $cs['address_to'] = $address;
                            $cs['action_type'] = 'ship';
                            // Зафиксируем в лог
                            ContainerLog::create($cs);

                            // Зафиксируем в import_logs
                            $import_log = ImportLog::where(['container_task_id' => $container_task->id, 'container_number' => $container_number])->first();
                            $import_log->state = 'not_selected';
                            $import_log->save();
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

            // Автоматическое создание заявки на прием
            if ($container_task->status == 'open' && $container_task->task_type == 'ship' && $container_task->trans_type == 'auto' && $data['order_auto'] == 'true') {
                $data['task_type'] = 'receive';
                $data['trans_type'] = 'auto';
                $data['status'] = 'waiting';
                $data['kind'] = 'automatic';
                $container_task_auto = ContainerTask::create($data);
                $container_task_auto->container_ids = json_encode($container_ids);
                $container_task_auto->save();

                $container_task->child_id = $container_task_auto->id;
                $container_task->save();

                foreach ($container_ids as $container_id => $container_number) {
                    ImportLog::create([
                        'container_task_id' => $container_task_auto->id, 'container_number' => $container_number,
                        'user_id' => $user_id, 'ip' => $_SERVER['REMOTE_ADDR'], 'state' => 'not_posted', 'status' => 'ok',
                        'comments' => 'Успешно'
                    ]);
                }
            }

            DB::commit();

            return response('Заявка успешно оформлен', 200);
        } catch (\Exception $exception) {
            DB::rollBack();
            return response('Ошибка с сервером: '. $exception, 500);
        }
    }

    public function getInfoForContainer(Request $request)
    {
        $session = Session::where(['user_id' => Auth::id()])->first();
        $container_number = $request->input('container_number');
        $technique_id = $request->input('technique_id');
        $container = Container::where('number', 'like', '%'.$container_number)->first();
        if ($container) {
            $container_stock = ContainerStock::where(['container_id' => $container->id])->first();
            if ($container_stock) {
                $container_address = $container_stock->container_address;
                $isCustoms = ($container_stock->customs == 'yes') ? 'Да' : 'Нет';

                if($session->ids == null && ($technique_id != 4 && $technique_id != 10)) {
                    return response(
                        "Контейнер: <span style='color: red;'>".$container->number."<br> ($container->company, $container_stock->state, $container->container_type, $isCustoms)</span> <br>Адрес: $container_address->name <br><span style='color:red'>Выберите стропальщика</span>",
                        404
                    );
                }

                if(($session->zone_id == 'SPR' || $session->zone_id == 'SPK') AND ($container_address->zone == 'SPR' || $container_address->zone == 'SPK')) {

                } else {
                    if($container_address->zone != $session->zone_id AND $container_address->zone != 'BUFFER' AND $container_address->zone != 'DAMU_IN' AND $container_address->zone != 'DAMU_OUT' AND $container_address->zone != 'ZTCIA') {
                        return response(
                            "Контейнер: <span style='color: red;'>".$container->number."<br> ($container->company, $container_stock->state, $container->container_type, $isCustoms)</span> находиться не вашем зоне.  <br>Адрес: $container_address->name",
                            404
                        );
                    }
                }

                if ($container_address->name == 'damu_in' && $container_stock->status == 'incoming') {
                    return response([
                        'data' => [
                            'text' => "Контейнер: <span style='color: red;'>".$container->number."<br> ($container->company, $container_stock->state, $container->container_type, $isCustoms)</span> необходимо <span style='color: green;'>РАЗМЕСТИТЬ.</span>",
                            'event' => 1,
                            'container_id' => $container->id,
                            'container_number' => $container->number,
                            'current_container_address' => $container_address->name,
                            'isCustoms' => $container_stock->customs
                        ]
                    ], 200);
                }
                if ($container_address->name == 'damu_in' && $container_stock->status == 'cancel') {
                    return response("Контейнер №".$container->number." найдено. Контейнер находится в процессе отмены", 403);
                }
                if($container_address->name == 'damu_out' && $container_stock->status == 'shipped') {
                    return response("Контейнер №".$container->number." найдено. Контейнер находится в зоне выдачи", 403);
                }
                if ($container_address->name != 'damu_in' && $container_stock->status == 'cancel') {
                    return response("Контейнер №".$container->number." найдено. Контейнер находится в процессе отмены", 403);
                }
                if ($container_address->name == 'damu_in' && $container_stock->status == 'edit') {
                    return response("Контейнер №".$container->number." найдено. Контейнер находиться в процессе редактирование", 403);
                }
                if($container_stock->status == 'received') {
                    return response([
                        'data' => [
                            'text' => "Контейнер: <span style='color: red;'>".$container->number."<br> ($container->company, $container_stock->state, $container->container_type, $isCustoms)</span>  <br>Адрес: $container_address->name",
                            'event' => 3,
                            'container_id' => $container->id,
                            'container_number' => $container->number,
                            'current_container_address' => $container_address->name,
                            'isCustoms' => $container_stock->customs
                        ]
                    ], 200);
                }
                if($container_stock->status == 'in_order') {
                    return response([
                        'data' => [
                            'text' => "Контейнер: <span style='color: red;'>".$container->number."<br> ($container->company, $container_stock->state, $container->container_type, $isCustoms)</span>  <br>Адрес: $container_address->name",
                            'event' => 2,
                            'container_id' => $container->id,
                            'container_number' => $container->number,
                            'current_container_address' => $container_address->name,
                            'isCustoms' => $container_stock->customs
                        ]
                    ], 200);
                }
            } else {
                return response("Контейнер №".$container_number." отсутствует в остатке", 404);
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

        /*if ($container_address->zone == 'SPR' || $container_address->zone == 'SPK') {
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
            }
        } else {
            return response('Можно перемещать контейнера', 200);
        }*/

        return response('Можно перемещать контейнера', 200);
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
            $container_dataset = [];
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
                    if ($this->isRussian($arr['A'])) {
                        continue;
                    }
					if (empty($arr['A'])) {
						continue;
					}
					if ($container_task->task_type == 'receive') {
                        $number = trim(strtoupper($arr['A']));
                        $company = trim(strtoupper($arr['B']));
                        $container_type = (empty($arr['C'])) ? '40' : (int) $arr['C'];
                        $state = (empty($arr['D'])) ? '' : $arr['D'];
                        $custom = (mb_strtolower($arr['E']) == 'да') ? 'yes' : 'not';
                        $car_number_carriage = (isset($arr['F'])) ? $arr['F'] : null;
                        $datetime_submission = (isset($arr['G'])) ? $arr['G'] : null;
                        $datetime_arrival = (isset($arr['H'])) ? $arr['H'] : null;
                        $contractor = (isset($arr['K'])) ? $arr['K'] : null;
                        $note = (isset($arr['L'])) ? $arr['L'] : null;
                        $seal_number_document = (isset($arr['I'])) ? $arr['I'] : null;
                        $seal_number_fact = (isset($arr['J'])) ? $arr['J'] : null;

                        if(is_null($datetime_submission) OR is_null($datetime_arrival)) {
                            continue;
                        }
                    } else {
                        $number = trim(strtoupper($arr['A']));
                        $company = trim(strtoupper($arr['B']));
                        $car_number_carriage = (isset($arr['C'])) ? $arr['C'] : null;
                        $seal_number_fact = (isset($arr['D'])) ? $arr['D'] : null;
                        $fio_driver = (isset($arr['E'])) ? $arr['E'] : null;
                        $contractor = (isset($arr['F'])) ? $arr['F'] : null;
                        $note = (isset($arr['G'])) ? $arr['G'] : null;
                        $seal_number_document = null;
                        $datetime_submission = null;
                        $datetime_arrival = null;
                        $custom = null;
                        $state = '';
                        $container_type = '40';
                    }

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
                            $container = Container::create(['number' => $number, 'company' => $company, 'container_type' => (string)$container_type]);
                        }
                    } else {
                        // На отбор
                        if (!$container) {
                            $data_import['status'] = 'not';
                            $data_import['comments'] = 'Контейнер отсутствует в остатке';
                            ImportLog::create($data_import);
                            continue;
                        }
                    }

                    $container_ids[$container->id] = $number;
                    $container_dataset[$container->id] = [
                        'state' => $state, 'customs' => $custom, 'car_number_carriage' => $car_number_carriage, 'datetime_submission' => $datetime_submission,
                        'datetime_arrival' => $datetime_arrival, 'contractor' => $contractor, 'note' => $note, 'seal_number_document' => $seal_number_document,
                        'seal_number_fact' => $seal_number_fact, 'container_id' => $container->id, 'container_number' => $number, 'company' => $company
                    ];

                    // Записываем истории об импорте
                    if ($data['task_type'] == 'receive') {
                        if (ContainerStock::exists($container->id)) {
                            $data_import['status'] = 'not';
                            $data_import['comments'] = 'Контейнер уже имеется в остатки';
                            ImportLog::create($data_import);
                        } else {
                            $data_import['status'] = 'ok';
                            $data_import['comments'] = 'Успешно';
                            ImportLog::create($data_import);
                        }
                    } else {
                        if (ContainerStock::is_shipping($container->id)) {
                            $data_import['status'] = 'ok';
                            $data_import['comments'] = 'Успешно';
                            ImportLog::create($data_import);
                        } else {
                            $data_import['status'] = 'not';
                            $data_import['comments'] = 'Контейнер отсутствует в остатке';
                            ImportLog::create($data_import);
                        }
                    }
                }
            }

            if ($container_task->isSuccessImport()) {
                if ($data['task_type'] == 'receive') {
                    // добавляем в остатки
                    $container_address_dmu_in = ContainerAddress::whereName('damu_in')->first();
                    foreach ($container_ids as $container_id => $container_number) {
                        $dataset = $container_dataset[$container_id];
                        $dataset['container_task_id'] = $container_task->id;
                        $dataset['status'] = 'incoming';
                        $dataset['container_address_id'] = $container_address_dmu_in->id;
                        $container_stock = ContainerStock::where(['container_id' => $container_id, 'container_address_id' => 1385])->first();
                        if ($container_stock) {
                            $container_stock->container_task_id = $container_task->id;
                            $container_stock->status = 'incoming';
                            $container_stock->container_address_id = $container_address_dmu_in->id;
                            $container_stock->save();
                        } else {
                            $container_stock = ContainerStock::where(['container_id' => $container_id, 'container_address_id' => $container_address_dmu_in->id])->first();
                            if (!$container_stock) {
                                ContainerStock::create($dataset);
                            }
                        }
                        $cs['start_date'] = Carbon::now();
                        $dataset['user_id'] = Auth::id();
                        $dataset['operation_type'] = 'incoming';
                        $dataset['address_from'] = $container_task->trans_type;
                        $dataset['address_to'] = $container_address_dmu_in->name;
                        $dataset['action_type'] = 'reception';
                        // Зафиксируем в лог
                        ContainerLog::create($dataset);

                        // Зафиксируем в import_logs
                        $import_log = ImportLog::where(['container_task_id' => $container_task->id, 'container_number' => $container_number])->first();
                        $import_log->state = 'not_posted';
                        $import_log->save();
                    }
                } else {
                    foreach ($container_ids as $container_id => $container_number) {
                        $container_stock = ContainerStock::where(['container_id' => $container_id])->first();
                        if ($container_stock) {
                            $container_stock->container_task_id = $container_task->id;
                            $container_stock->status = 'in_order';
                            $container_stock->save();
                            $address = $container_stock->container_address->name;
                            $dataset = $container_stock->attributesToArray();
                            $cs['start_date'] = Carbon::now();
                            $dataset['user_id'] = Auth::id();
                            $dataset['operation_type'] = 'in_order';
                            $dataset['container_number'] = $container_number;
                            $dataset['address_from'] = $address;
                            $dataset['address_to'] = $address;
                            $dataset['action_type'] = 'ship';
                            // Зафиксируем в лог
                            ContainerLog::create($dataset);

                            // Зафиксируем в import_logs
                            $import_log = ImportLog::where(['container_task_id' => $container_task->id, 'container_number' => $container_number])->first();
                            $import_log->state = 'not_selected';
                            $import_log->save();
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

            // Автоматическое создание заявки на прием
            if ($container_task->status == 'open' && $container_task->task_type == 'ship' && $container_task->trans_type == 'auto' && $data['order_auto'] == 'true') {
                $data['task_type'] = 'receive';
                $data['trans_type'] = 'auto';
                $data['status'] = 'waiting';
                $data['kind'] = 'automatic';
                $container_task_auto = ContainerTask::create($data);
                $container_task_auto->container_ids = json_encode($container_ids);
                $container_task_auto->save();

                $container_task->child_id = $container_task_auto->id;
                $container_task->save();

                foreach ($container_ids as $container_id => $container_number) {
                    ImportLog::create([
                        'container_task_id' => $container_task_auto->id, 'container_number' => $container_number,
                        'user_id' => Auth::id(), 'ip' => $_SERVER['REMOTE_ADDR'], 'state' => 'not_posted', 'status' => 'ok',
                        'comments' => 'Успешно'
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('kt.kt_operator');
        } catch (\Exception $exception) {
            DB::rollBack();
        }
    }

    public function checkingTheContainerForDispensing(Request $request)
    {
        $container_id = $request->input('container_id');
        $container_stock = ContainerStock::where(['container_id' => $container_id])->first();
        if ($container_stock) {
            $container_address = $container_stock->container_address;
            $name = $container_address->name;
            $arr = explode("-", $name);
            $floor = $container_address->floor;

            if ($floor == 4) {
                return response('Контейнер готов к отбору', 200);
            } else {
                /*$container_address = $container_stock->container_address;
                if ($container_address->zone == 'SPR' || $container_address->zone == 'SPK') {
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
                } else {
                    return response('Контейнер готов к отбору', 200);
                }*/

                return response('Контейнер готов к отбору', 200);
            }
        } else {
            return response([
                'data' => [
                    'text' => '<span style="color: red; font-size: 30px;">Отсутствует заявка на выдачу</span>'
                ]
            ], 403);
        }
    }

    // Метод для выдачи контейнера
    public function shippingContainerChange(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();
            $container = Container::findOrFail($data['container_id']);
            if ($container) {
                $container_stock = ContainerStock::where(['container_id' => $container->id, 'status' => 'in_order'])->first();
                $current_address_name = $container_stock->container_address->name;
                $container_address_dam_out = ContainerAddress::whereName('damu_out')->first();
                if ($container_stock) {
                    $container_stock->container_address_id = $container_address_dam_out->id;
                    $container_stock->status = 'shipped';
                    $container_stock->save();

                    $cs = $container_stock->attributesToArray();
                    $cs['start_date'] = date('Y-m-d H:i:s', $data['start_date'] / 1000);
                    $cs['user_id'] = Auth::id();
                    $cs['container_number'] = $container->number;
                    $cs['operation_type'] = 'shipped';
                    $cs['technique_id'] = $data['technique_id'];
                    $cs['address_from'] = $current_address_name;
                    $cs['address_to'] = $container_address_dam_out->name;
                    $cs['action_type'] = 'pick';
                    $cs['slinger_ids'] = $data['slinger_ids'];

                    // Зафиксируем в лог
                    ContainerLog::create($cs);

                    // Зафиксируем в таблицу import_logs и если заявка по авто, то автоматические удаляем из стока текущего контейнера.
                    $container_task = $container_stock->container_task;
                    if ($container_task) {
                        if ($container_task->trans_type == 'auto') {
                            ContainerTask::complete_part($container_task->id, $container->id);
                        } else {
                            $import_log = ImportLog::where(['container_task_id' => $container_task->id, 'container_number' => $container->number])->first();
                            $import_log->state = 'selected';
                            $import_log->save();
                        }
                    }
                    DB::commit();
                    return response(['data' => '<span style="font-size: 30px;line-height: 30px;color: #fff;">Контейнер успешно выдан!!!</span>'], 200);
                } else {
                    return response(['data' => 'В таблице остатки не найдено запись на отбор'], 404);
                }
            } else {
                return response(['data' => 'Не найден контейнер'], 404);
            }
        } catch (\Exception $exception){
            DB::rollBack();
            echo  $exception->getMessage();
            exit();
        }
    }

    public function receiveContainerByKeyboard(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();
            $data['custom'] = (mb_strtolower($data['custom']) == 'да') ? 'yes' : 'not';
            $data['user_id'] = $user_id = Auth::id();
            $data['contractor'] = (isset($data['contractor'])) ? $data['contractor'] : null;
            $data['note'] = (isset($data['note'])) ? $data['note'] : null;
            $data['seal_number_document'] = (isset($data['seal_number_document'])) ? $data['seal_number_document'] : null;
            $data['seal_number_fact'] = (isset($data['seal_number_fact'])) ? $data['seal_number_fact'] : null;
            $container_task = ContainerTask::create($data);
            $number = trim(strtoupper($data['container_number']));
            $data_import = [
                'container_task_id' => $container_task->id, 'container_number' => $number,
                'user_id' => $user_id, 'ip' => $_SERVER['REMOTE_ADDR']
            ];

            if(strlen($number) < 11) {
                $data_import['status'] = 'not';
                $data_import['comments'] = 'Номер контейнера не правильно.';
                ImportLog::create($data_import);
            }

            if(strlen($number) > 11) {
                $data_import['status'] = 'not';
                $data_import['comments'] = 'Номер контейнера не правильно.';
                ImportLog::create($data_import);
            }

            $str = substr($number,0,4);
            if (!preg_match('/^[A-Z]{4}$/', $str)) {
                $data_import['status'] = 'not';
                $data_import['comments'] = 'Номер контейнера должно начаться с 4-х букв латинского алфавита';
                ImportLog::create($data_import);
            }

            $str = substr($number,4,7);
            if (!preg_match('/^[0-9]{7}$/', $str)) {
                $data_import['status'] = 'not';
                $data_import['comments'] = 'В номере контейнера должно быть 7 цифр';
                ImportLog::create($data_import);
            }

            $container = Container::whereNumber($number)->first();
            if ($data['task_type'] == 'receive') {
                // Размещение
                if (!$container) {
                    $container_type = (string) $data['container_type'];
                    $container = Container::create(['number' => $number, 'company' => $data['company'], 'container_type' => $container_type]);
                }
            } else {
                // На отбор
                if (!$container) {
                    $data_import['status'] = 'not';
                    $data_import['comments'] = 'Контейнер отсутствует в остатке';
                    ImportLog::create($data_import);
                }
            }

            if (!$container) {
                return response()->json("Контейнер $number не найден.", 403);
            }

            // Записываем истории об импорте
            if ($data['task_type'] == 'receive') {
                if (ContainerStock::exists($container->id)) {
                    $data_import['status'] = 'not';
                    $data_import['comments'] = 'Контейнер уже имеется в остатки';
                    ImportLog::create($data_import);
                } else {
                    $data_import['status'] = 'ok';
                    $data_import['comments'] = 'Успешно';
                    ImportLog::create($data_import);
                }
            } else {
                if (ContainerStock::is_shipping($container->id)) {
                    $data_import['status'] = 'ok';
                    $data_import['comments'] = 'Успешно';
                    ImportLog::create($data_import);
                } else {
                    $data_import['status'] = 'not';
                    $data_import['comments'] = 'Контейнер отсутствует в остатке';
                    ImportLog::create($data_import);
                }
            }

            if ($container_task->isSuccessImport()) {
                if ($data['task_type'] == 'receive') {
                    $container_address_dmu_in = ContainerAddress::whereName('damu_in')->first();
                    $container_stock = ContainerStock::where(['container_id' => $container->id, 'container_address_id' => 1385])->first();
                    if ($container_stock) {
                        $container_stock->container_task_id = $container_task->id;
                        $container_stock->status = 'incoming';
                        $container_stock->container_address_id = $container_address_dmu_in->id;
                        $container_stock->save();
                    } else {
                        // добавляем в остатки
                        $container_stock = ContainerStock::create([
                            'container_task_id' => $container_task->id, 'container_id' => $container->id, 'container_address_id' => $container_address_dmu_in->id, 'state' => $data['state'], 'company' => $data['company'],
                            'customs' => $data['custom'], 'car_number_carriage' => $data['car_number_carriage'], 'seal_number_document' => $data['seal_number_document'],
                            'seal_number_fact' => $data['seal_number_fact'], 'datetime_submission' => $data['datetime_submission'], 'datetime_arrival' => $data['datetime_arrival'],
                            'contractor' => $data['contractor'], 'note' => $data['note'], 'status' => 'incoming'
                        ]);
                    }

                    if ($container_stock) {
                        $cs = $container_stock->attributesToArray();
                        $cs['start_date'] = Carbon::now();
                        $cs['user_id'] = Auth::id();
                        $cs['container_number'] = $container->number;
                        $cs['operation_type'] = 'incoming';
                        $cs['address_from'] = $container_task->trans_type;
                        $cs['address_to'] = $container_address_dmu_in->name;
                        $cs['action_type'] = 'put';
                        // Зафиксируем в лог
                        ContainerLog::create($cs);
                    }
                } else {
                    $container_stock = ContainerStock::where(['container_id' => $container->id])->first();
                    if ($container_stock) {
                        $container_stock->container_task_id = $container_task->id;
                        $container_stock->status = 'in_order';
                        $container_stock->save();

                        $address = $container_stock->container_address->name;

                        $cs = $container_stock->attributesToArray();
                        $cs['start_date'] = Carbon::now();
                        $cs['user_id'] = Auth::id();
                        $cs['container_number'] = $container->number;
                        $cs['operation_type'] = 'in_order';
                        $cs['address_from'] = $address;
                        $cs['address_to'] = $address;
                        $cs['action_type'] = 'pick';

                        // Зафиксируем в лог
                        ContainerLog::create($cs);

                        // Зафиксируем в import_logs
                        $import_log = ImportLog::where(['container_task_id' => $container_task->id, 'container_number' => $container->number])->first();
                        $import_log->state = 'not_selected';
                        $import_log->save();
                    }
                }
                $container_task->status = 'open';
                $container_task->save();
            } else {
                $container_task->status = 'failed';
                $container_task->save();
            }

            $container_task->container_ids = json_encode([$container->id => $container->number]);
            $container_task->save();
            DB::commit();

            return response('Заявка успешно оформлен', 200);
        } catch (\Exception $exception) {
            DB::rollBack();
            return response("Ошибка с сервером: $exception", 500);
        }
    }

    public function getContainerShips()
    {
        $container_ships = Car::select('id', 'gov_number')->where('ours', 'yes')->get();
        $container_ships->push([
            'id'=> 0,
            'gov_number' => 'Другой'
        ]);
        return response()->json($container_ships);
    }

    public function movingContainerToAnotherZone(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();
            if (is_null($data['other_container_ship'])) {
                $car = Car::findOrFail($data['container_ship_id']);
                $car_number = $car->gov_number;
            } else {
                $car_number = $data['other_container_ship'];
            }

            $container_address = ContainerAddress::whereName('buffer')->first();

            if ($container_address) {
                $container = Container::find($data['container_id']);
                if ($container) {
                    $container_stock = ContainerStock::where(['container_id' => $container->id])->first();
                    $current_address_name = $container_stock->container_address->name;
                    $container_stock->container_address_id = $container_address->id;
                    $container_stock->save();

                    $cs = $container_stock->attributesToArray();
                    $cs['start_date'] = date('Y-m-d H:i:s', $data['start_date'] / 1000);
                    $cs['user_id'] = Auth::id();
                    $cs['container_number'] = $container->number;
                    $cs['operation_type'] = 'received';
                    $cs['technique_id'] = $data['technique_id'];
                    $cs['address_from'] = $current_address_name;
                    $cs['address_to'] = $data['zone']." | ".$container_address->name;
                    $cs['action_type'] = 'move_another_zone';
                    $cs['car_number_carriage'] = $car_number;
                    $cs['slinger_ids'] = $data['slinger_ids'];

                    // Зафиксируем в лог
                    ContainerLog::create($cs);
                    DB::commit();
                    return response(['data' => '<span style="font-size: 30px;line-height: 30px;color: #fff;">Контейнер успешно перемещен!!!</span>'], 200);
                } else {
                    return response(['data' => 'Не найден контейнер'], 404);
                }
            } else {
                return response(['data' => 'Не найден адрес зоны'], 404);
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            echo $exception->getMessage();
            exit();
        }
    }

    public function sendMessageSam($data)
    {
        $beautymail = app()->make(\Snowfire\Beautymail\Beautymail::class);
        $beautymail->send('emails.samsung2', [
            'data' => $data,
        ], function($message)
        {
            $message
                ->from('webcont@dlg.kz')
                ->to('samsung-clients@htl.kz')
                //->to('kairat.ubukulov@htl.kz')
                ->subject('Уведомление о приходе контейнеров по ж/д');
        });
    }

    public function noticeAboutTechnique(Request $request)
    {
        $technique_id = $request->input('technique_id');
        $technique = Technique::findOrFail($technique_id);

        $data = [
            'technique' => $technique->name,
            'user' => Auth::user()->full_name
        ];

        if($request->has('reason')) {
            $reason = $request->input('reason');
            $technique->status = 'not_works';
            $technique->save();

            ContainerLog::create([
                'user_id' => Auth::id(), 'operation_type' => 'crane', 'action_type' => 'crane_not_works', 'note' => $reason
            ]);

            $data['note'] = $reason;
            $subject = "WEBCONT. " . $technique->name . " не работает";
        } else {
            $technique->status = 'works';
            $technique->save();

            ContainerLog::create([
                'user_id' => Auth::id(), 'operation_type' => 'crane', 'action_type' => 'crane_works'
            ]);

            $subject = "WEBCONT. " . $technique->name . " работает";
        }

        $data['status'] = ($technique->status == 'works') ? 'Работает' : 'Не работает';
        $data['date'] = date('d.m.Y H:i');

        $beautymail = app()->make(\Snowfire\Beautymail\Beautymail::class);
        $beautymail->send('emails.crane', [
            'data' => $data,
        ], function($message) use ($subject)
        {
            $message
                ->from('webcont@dlg.kz')
                ->to('webcont.status@dlg.kz')
//                ->to('kairat.ubukulov@htl.kz')
                ->subject($subject);
        });

        return response('success', 200);
    }

    public function getSlingers()
    {
        $result = User::whereIn('company_id', [31, 223])
                ->selectRaw('id, full_name')
                ->selectRaw('(SELECT users_histories.status FROM users_histories WHERE users_histories.user_id=users.id ORDER BY users_histories.id DESC LIMIT 1) as status')
                ->whereIn('position_id', [91,92,93,153,174])
                ->whereNotIn('id', Session::getIds())
                ->orderBy('full_name')
                ->get();
        $users = [];

        foreach($result as $item) {
            if($item->status != 'works') continue;
            $arr =explode(' ', $item->full_name);
            $users[] = [
                'id' => $item->id,
                'full_name' => $arr[0]. " " . mb_substr($arr[1],0,1) . "."
            ];
        }
        return response()->json($users);
    }

    public function sendMySettingsToSession(Request $request)
    {
        $zone_id = $request->input('zone_id');
        $technique_id = $request->input('technique_id');
        $slinger_ids = $request->input('slinger_ids');

        $session = Session::where(['user_id' => Auth::id()])->first();
        if($session) {
            $session->zone_id = $zone_id;
            $session->technique_ids = $technique_id;
            $session->ids = ($zone_id == 'RICH') ? null : $slinger_ids;
            $session->save();

            return response('success');
        }

        return response('unknown session', 403);
    }

    public function cancelMySettingsToSession(Request $request) {
        $session = Session::where(['user_id' => Auth::id()])->first();
        if($session) {
            $session->zone_id = null;
            $session->technique_ids = null;
            $session->ids = null;
            $session->save();

            return response('success');
        }
    }
}
