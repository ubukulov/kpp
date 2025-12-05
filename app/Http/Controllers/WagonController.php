<?php

namespace App\Http\Controllers;

use App\Models\Container;
use App\Models\User;
use App\Models\Wagon\WagonLog;
use App\Models\Wagon\WagonStock;
use App\Models\Wagon\WagonStockContainer;
use App\Models\Wagon\WagonTask;
use App\Models\Wagon\WagonTrack;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use PDF;
use Illuminate\Support\Facades\DB;
use App\Services\WagonService;

class WagonController
{
    protected WagonService $wagonService;
    public function __construct(WagonService $wagonService)
    {
        $this->wagonService = $wagonService;
    }

    public function getWagonTasks(): \Illuminate\Http\JsonResponse
    {
        $wagonTasks = WagonTask::with([
            'user:id,full_name',
            'items' => function ($query) {
                $query->select([
                    'id',
                    'wagon_task_id',
                    'wagon_id',
                    'wagon_number',
                    'wagon_track_id',
                    'wagon_track_destination_id',
                    'cargo_name',
                    'owner_name',
                    'reception_datetime',
                    'country_code',
                    'state',
                    'ordinal_number',
                    'destination_station',
                    'direction',
                    'shipper_name',
                    'status',
                    'operation_type',
                    'consignee_name',
                ]);
            },
            'items.wagon:id,number,length',
            'items.wagon_track:id,number',
            'items.wagon_track_destination:id,number',
            'items.containers:id,container_number,wagon_task_item_id',
        ])
            ->orderBy('id', 'desc')
            ->get();
        return response()->json($wagonTasks);
    }

    public function createWagonTask(Request $request): \Illuminate\Http\JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = $request->except('file');
            // Подготовка папок для сохранение файлов
            $dir = '/wagon_files/'. substr(md5(microtime()), mt_rand(0, 30), 2);
            if(!File::isDirectory(public_path(). $dir)){
                File::makeDirectory(public_path(). $dir, 0777, true);
            }

            $upload_file = $request->file('file');
            $filename = substr(md5(time()), 0, 5) . "_" . $upload_file->getClientOriginalName();
            $path_to_file = $dir.'/'.$filename;
            $upload_file->move(public_path() . $dir, $filename);

            $data['user_id'] = Auth::id();
            $data['upload_file'] = $path_to_file;
            $data['date_time'] = Carbon::now();

            $wagonTask = WagonTask::create($data);

            $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify(public_path($path_to_file));
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
            $spreadsheet = $reader->load(public_path($path_to_file));
            $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

            if($data['type'] == 'submission') {
                // заявка на подачу
                $result = $this->wagonService->createWagonTaskForSubmission($wagonTask, $sheetData, $request->user());
            } else if($data['type'] == 'cleaning') {
                // заявка на уборку
                $result = $this->wagonService->createWagonTaskForCleaning($wagonTask, $sheetData, $request->user());
            } else {
                // заявка на маневровые работы
                $result = $this->wagonService->createWagonTaskForShunting($wagonTask, $sheetData, $request->user());
            }

            if (!empty($result['errors'])) {
                DB::rollBack();
                return response()->json(['message' => 'Ошибки при обработке файла', 'errors' => $result['errors']], 400, [], JSON_UNESCAPED_UNICODE);
            }

            DB::commit();

            return response()->json([
                'message' => 'Заявка успешно создан'
            ], 200, [], JSON_UNESCAPED_UNICODE);

        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json($exception->getMessage() . " | On line: " . $exception->getLine(), 500);
        }
    }

    public function getWagonStocks(Request $request): \Illuminate\Http\JsonResponse
    {
        $group = $request->input('group');
        $wagonStocks = WagonStock::with([
            'wagon',
            'wagon_task',
            'wagon_track',
            'containers.container'
        ]);

        if($group == 'ilc') {
            $wagonStocks = $wagonStocks->whereIn('wagon_track_id', [1,2,3,4,5,6,7,13,14,15,16,17,18,19]);
        } else if($group == 'resident') {
            $wagonStocks = $wagonStocks->whereIn('wagon_track_id', WagonTrack::where('company_id', '!=', 31)->pluck('id'));
        } else if($group == 'warehouse') {
            $wagonStocks = $wagonStocks->whereIn('wagon_track_id', [8,9,10,11,12]);
        }

        $wagonStocks = $wagonStocks->get();

        $stats = [
            // порожний вагон
            'empty_wagons' => $wagonStocks->filter(function ($w) {
                return $w->state === 'Порожний' && $w->containers->isEmpty();
            })->count(),

            // груженный вагон c грузом
            'loaded_wagons' => $wagonStocks->filter(function ($w) {
                return $w->state === 'Груженный' && $w->containers->isEmpty();
            })->count(),

            // груженный вагон с груженным контейнером
            'loaded_with_loaded_container' => $wagonStocks->filter(function ($w) {
                return $w->state === 'Груженный'
                    && $w->containers->isNotEmpty();
            })->count(),

            // груженный вагон с порожним контейнером
            'loaded_with_empty_container' => $wagonStocks->filter(function ($w) {
                return $w->state === 'Порожний'
                    && $w->containers->isNotEmpty();
            })->count(),

            // маневровые работы
            'shunting_wagons' => $wagonStocks->filter(function ($w) {
                return $w->status === 'moving_request';
            })->count(),

            // Всего
            'all_wagons' => $wagonStocks->count()
        ];

        // Группировка по пути (wagon_track_id)
        $grouped = $wagonStocks->groupBy('wagon_track_id')->map(function ($items) {
            return $items
                ->sortBy('ordinal_number')
                ->values()
                ->map(function ($wagonStock) {
                    $wagon = $wagonStock->wagon;
                    $wagonTrack = $wagonStock->wagon_track;
                    $destination_track = ($wagonStock->wagon_track_destination) ?: null;
                    return [
                        'id' => $wagonStock->id,
                        'ordinal_number' => $wagonStock->ordinal_number,
                        'status' => $wagonStock->status,
                        'state' => $wagonStock->state,
                        'status_label' => $wagonStock->wagon_state_label,
                        'wagon_id' => $wagon->id,
                        'wagon_number' => $wagon->number,
                        'wagon_track_id' => $wagonTrack->id,
                        'wagon_track' => $wagonTrack->number,
                        'operation_type' => $wagonStock->operation_type,
                        'wagon_length' => $wagon->length,
                        'owner_name' => $wagonStock->owner_name,
                        'direction' => $wagonStock->direction,
                        'reception_datetime' => $wagonStock->reception_datetime,
                        'updated_at' => $wagonStock->updated_at,
                        'container_number' => $wagonStock->wagon_containers_number,
                        'destination_track' => $destination_track,
                        'containers' => ($wagonStock->containers) ?: null,
                        'gap' => $wagonStock->gap
                    ];
            });
        });

        return response()->json([
            'wagons' => $grouped,
            'stats' => $stats
        ]);
    }

    public function getWagonTracks(): \Illuminate\Http\JsonResponse
    {
        return response()->json(WagonTrack::with('company')->orderBy('number')->get());
    }

    public function updateWagonStock(Request $request): \Illuminate\Http\JsonResponse
    {
        $user_type = $request->input('user_type');
        $ordinalNumber  = (int) $request->input('ordinal_number');
        $wagonTrackId   = (int) $request->input('wagon_track_id');
        $gap            = $request->input('gap');

        $wagonStock = WagonStock::findOrFail($request->input('wagon_stock_id'));
        $wagonTask = $wagonStock->wagon_task;
        $address_from = $wagonStock->wagon_track->number;
        if ($wagonTrackId == 0) {
            $wagonStock->update([
                'gap' => $gap == 'true',
            ]);

            return response()->json([
                'message' => 'Вагон успешно обновлен',
                'data' => $wagonStock
            ], 200);
        }

        DB::beginTransaction();
        try {
            if ($user_type == 'Dispatcher') {
                $currentTrackId = (int) $wagonStock->wagon_track_id;
                $currentOrdinal = (int) $wagonStock->ordinal_number;

                // Если та же позиция — ничего не делаем
                if ($currentTrackId === $wagonTrackId && $currentOrdinal === $ordinalNumber) {
                    // noop
                } else {
                    // 1) Перемещение внутри того же трека
                    if ($currentTrackId === $wagonTrackId) {
                        if ($currentOrdinal < $ordinalNumber) {
                            // сдвигаем промежуточные записи вверх (уменьшаем на 1)
                            WagonStock::where('wagon_track_id', $currentTrackId)
                                ->whereBetween('ordinal_number', [$currentOrdinal + 1, $ordinalNumber])
                                ->lockForUpdate()
                                ->update([
                                    'ordinal_number' => DB::raw('ordinal_number - 1')
                                ]);
                        } else {
                            // сдвигаем промежуточные записи вниз (увеличиваем на 1)
                            WagonStock::where('wagon_track_id', $currentTrackId)
                                ->whereBetween('ordinal_number', [$ordinalNumber, $currentOrdinal - 1])
                                ->lockForUpdate()
                                ->update([
                                    'ordinal_number' => DB::raw('ordinal_number + 1')
                                ]);
                        }

                        // обновляем сам вагон
                        $wagonStock->update([
                            'ordinal_number' => $ordinalNumber,
                            'status' => 'movement'
                        ]);
                    } else {
                        // 2) Перемещение в другой трек
                        // Сдвигаем целевой трек: все ordinal >= $ordinalNumber +1
                        WagonStock::where('wagon_track_id', $wagonTrackId)
                            ->where('ordinal_number', '>=', $ordinalNumber)
                            ->lockForUpdate()
                            ->update([
                                'ordinal_number' => DB::raw('ordinal_number + 1')
                            ]);

                        // Обновляем перемещаемый вагон (смена трека и позиции)
                        $wagonStock->update([
                            'wagon_track_id' => $wagonTrackId,
                            'ordinal_number' => $ordinalNumber,
                            'status' => 'movement'
                        ]);

                        // Сжимаем исходный трек: уменьшаем ordinal для тех, кто был после старой позиции
                        WagonStock::where('wagon_track_id', $currentTrackId)
                            ->where('ordinal_number', '>', $currentOrdinal)
                            ->lockForUpdate()
                            ->update([
                                'ordinal_number' => DB::raw('ordinal_number - 1')
                            ]);
                    }
                }

                // Ресеквенс (опционально, чтобы убрать дыры) — можно выполнить после всех обновлений
                $this->resequenceOrdinalNumbers($wagonStock->wagon_track_id);
                if (isset($currentTrackId) && $currentTrackId !== $wagonStock->wagon_track_id) {
                    $this->resequenceOrdinalNumbers($currentTrackId);
                }
            } else {
                $wagonStock->update([
                    'state' => $request->input('wagon_state')
                ]);
            }

            $wagonStock->refresh();
            $address_to = $wagonStock->wagon_track->number;

            $ws = $wagonStock->attributesToArray();

            $ws['wagon_number'] = $wagonStock->wagon->number;
            $ws['wagon_task_number'] = $wagonStock->wagon_task->number;
            $ws['address_from'] = $address_from;
            $ws['address_to'] = $address_to;
            $ws['status'] = 'movement';

            if($user_type == 'Dispatcher') {
                $ws['user_ids'] = implode(',', session()->get("shifts.user_".Auth::id()));
            }

            WagonLog::create($ws);

            $wagonTask->status = ($wagonTask->is_closing) ? 'closed' : 'processing';
            $wagonTask->save();

            DB::commit();

            return response()->json([
                'message' => 'Вагон успешно обновлен',
                'data' => $wagonStock
            ], 200);
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json([
                'message' => $exception->getMessage() . " | Строка ошибки: " . $exception->getLine() . " Файл: " . $exception->getFile()
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Вспомогательная функция: перенумеровывает ordinal_number подряд начиная с 1 для указанного wagon_track_id.
     */
    protected function resequenceOrdinalNumbers(int $wagonTrackId): void
    {
        $rows = WagonStock::where('wagon_track_id', $wagonTrackId)
            ->orderBy('ordinal_number')
            ->lockForUpdate()
            ->get();

        $i = 1;
        foreach ($rows as $row) {
            if ((int)$row->ordinal_number !== $i) {
                $row->ordinal_number = $i;
                $row->save();
            }
            $i++;
        }
    }

    // операции над несколькими вагонами сразу
    public function updateWagonsStock(Request $request): \Illuminate\Http\JsonResponse
    {
        DB::beginTransaction();
        try {
            $wagons = json_decode($request->input('selectedWagonsForm'), true);
            $commonTrackId = $request->input('commonTrackId') ?? null;
            $user_type = $request->input('user_type');

            foreach ($wagons as $wagon) {
                $wagonStock = WagonStock::findOrFail($wagon['wagon_stock_id']);
                $wagonTask = $wagonStock->wagon_task;

                $address_from = $wagonStock->wagon_track->number;

                if($user_type == 'Dispatcher') {
                    $wagonTrackId = (!is_null($commonTrackId)) ? $commonTrackId : $wagon['wagon_track_id'];
                    $address_to = WagonTrack::find($wagonTrackId)->number;

                    $wagonStock->update([
                        'wagon_track_id' => $wagonTrackId,
                        'ordinal_number' => $wagon['ordinal_number'],
                    ]);
                } else {
                    $address_to = $address_from;
                    $wagonStock->update([
                        'state' => $wagon['wagon_state']
                    ]);
                }

                if($wagonTask->type != 'cleaning') {
                    $wagonStock->status = 'movement';
                    $wagonStock->save();
                }

                $wagonStock->refresh();

                $ws = $wagonStock->attributesToArray();

                $containers = $wagonStock->containers;

                $ws['wagon_number'] = $wagonStock->wagon->number;
                $ws['wagon_task_number'] = $wagonStock->wagon_task->number;
                $ws['address_from'] = $address_from;
                $ws['address_to'] = $address_to;
                $ws['status'] = 'movement';

                if($user_type == 'Dispatcher') {
                    $ws['user_ids'] = implode(',', session()->get("shifts.user_".Auth::id()));
                }

                if(count($containers) > 0) {
                    foreach ($wagonStock->containers as $wagonStockContainer) {
                        $ws['container_id'] = $wagonStockContainer->container_id;
                        $ws['container_number'] = $wagonStockContainer->container_number;

                        WagonLog::create($ws);
                    }
                } else {
                    WagonLog::create($ws);
                }

                $wagonTask->status = ($wagonTask->is_closing) ? 'closed' : 'processing';
                $wagonTask->save();
            }

            DB::commit();

            return response()->json([
                'message' => 'Вагоны успешно обновлен',
            ], 200);

        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json($exception->getMessage(), 500);
        }
    }

    public function cleaningRequestWagon(Request $request): \Illuminate\Http\JsonResponse
    {
        DB::beginTransaction();
        try {
            $selectedWagons = json_decode($request->input('selectedWagons'), true);
            $wagonTasks = collect();
            foreach($selectedWagons as $selectedWagon) {
                if(in_array($selectedWagon['wagon_track_id'], [3,4,5]) && $selectedWagon['status'] == 'cleaning_request') { // 3,4,5 Ид путей
                    $wagonStock = WagonStock::find($selectedWagon['id']);
                    $address_from = $wagonStock->wagon_track->number;
                    $ws = $wagonStock->attributesToArray();
                    $wagonStockContainers = $wagonStock->containers;
                    $wagonTask = $wagonStock->wagon_task;

                    $ws['wagon_number'] = $selectedWagon['wagon_number'];
                    $ws['wagon_task_number'] = $wagonTask->number;
                    $ws['address_from'] = $address_from;
                    $ws['address_to'] = 'completed';
                    $ws['status'] = 'cleaning_confirmed';
                    $ws['user_ids'] = implode(',', session()->get("shifts.user_".Auth::id()));

                    if(!$wagonTasks->contains('id', $selectedWagon['id'])) {
                        $wagonTasks->push($wagonTask);
                    }

                    if(count($wagonStockContainers) > 0) {
                        foreach ($wagonStockContainers as $wagonStockContainer) {
                            $ws['container_id'] = $wagonStockContainer->container_id;
                            $ws['container_number'] = $wagonStockContainer->container_number;

                            WagonLog::create($ws);
                        }
                    }

                    if ($wagonStock->containers->isEmpty()) {
                        WagonLog::create($ws);
                    }

                    $wagonStock->delete();
                } else {
                    return response()->json(['message' => "Вагон " . $selectedWagon['wagon_number'] . " нет заявки на уборку либо вагон не стоит на выставочном пути"], 400, [], JSON_UNESCAPED_UNICODE);
                }
            }

            DB::commit();

            foreach($wagonTasks as $wagonTask) {
                $wagonTask->refresh()->load('stocks');
                $wagonTask->status = ($wagonTask->is_closing) ? 'closed' : 'processing';
                $wagonTask->save();
            }

            return response()->json([
                'message' => 'Вагоны успешно убраны'
            ]);
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json($exception->getMessage(), 500);
        }
    }

    public function printWagons(Request $request)
    {
        $wagonStocks = WagonStock::with(
            [
                'wagon',
                'wagon_track'
            ]
        )
            ->get()
            ->groupBy('wagon_track.number');

        if ($request->has('type') && $request->input('type') == 'pdf') {
            $pdf = PDF::loadView('pdf.wagons', compact('wagonStocks'));
            return $pdf->download(date("d.m.Y_H:i:s_") . "wagon_stocks.pdf");
        } else {
            $path = $this->wagonService->saveToExcel($wagonStocks);
            return response()->download(public_path($path));
        }
    }

    public function detachContainerFromWagon($wagonStockContainerId): \Illuminate\Http\JsonResponse
    {
        DB::beginTransaction();
        try {
            $wagonStockContainer = WagonStockContainer::find($wagonStockContainerId);
            if (!$wagonStockContainer) {
                return response()->json(['message' => 'Контейнер не найден у вагона'], 404, [], JSON_UNESCAPED_UNICODE);
            }

            $wagonStock = $wagonStockContainer->wagon_stock;

            $ws = $wagonStock->attributesToArray();

            $address_from = $wagonStock->wagon_track->number;

            $ws['wagon_number'] = $wagonStock->wagon->number;
            $ws['address_from'] = $address_from;
            $ws['address_to']   = $address_from;
            $ws['container_id'] = $wagonStockContainer->container_id;
            $ws['container_number'] = $wagonStockContainer->container_number;
            $ws['operation_type'] = 'unloading';
            $ws['status'] = 'detach_container';
            $ws['user_ids'] = is_array(session("shifts.user_" . Auth::id()))
                ? implode(',', session("shifts.user_" . Auth::id()))
                : null;

            // зафиксируем в логах
            WagonLog::create($ws);

            $wagonStockContainer->delete();

            DB::commit();

            return response()->json(['message' => 'Контейнер успешно откреплен от вагона'], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json([
                'message' => $exception->getMessage() . " | Строка ошибки: " . $exception->getLine() . " Файл: " . $exception->getFile()
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function attachContainerToWagon(Request $request): \Illuminate\Http\JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = json_decode($request->input('data'), true);
            $wagon = json_decode($request->input('wagon'), true);

            $wagonStock = WagonStock::find($wagon['id']);
            $wagonStockContainers = $wagonStock->containers;

            $container = Container::firstOrCreate(
                ['number' => $data['container_number']],
                ['container_type' => $data['container_type']]
            );

            // Проверка на дубликат
            if ($wagonStockContainers->contains('container_id', $container->id)) {
                return response()->json(['message' => 'Этот контейнер уже прикреплен к вагону'], 400, [], JSON_UNESCAPED_UNICODE);
            }


            $newType = (int)$data['container_type'];
            $existingTypes = $wagonStockContainers->pluck('container.container_type')->map(fn($v) => (int)$v)->toArray();

            $this->validateWagonCapacity($existingTypes, $newType);

            WagonStockContainer::create([
                'wagon_stock_id' => $wagonStock->id,
                'weight' => $data['weight'],
                'seal_number_document' => $data['seal_number_document'],
                'cargo_name' => $data['cargo_name'],
                'consignee_name' => $data['consignee_name'],
                'shipper_name' => $data['shipper_name'],
                'container_id' => $container->id,
                'container_number' => $container->number,
            ]);

            $ws = $wagonStock->attributesToArray();

            $address_to = $wagonStock->wagon_track->number;

            $ws['wagon_number'] = $wagonStock->wagon->number;
            $ws['address_from'] = "cloud";
            $ws['address_to']   = $address_to;
            $ws['container_id'] = $container->id;
            $ws['container_number'] = $container->number;
            $ws['operation_type'] = 'loading';
            $ws['status'] = 'attach_container';

            // зафиксируем в логах
            WagonLog::create($ws);

            DB::commit();

            $wagonStock->load('containers.container');

            return response()->json([
                'message' => 'Контейнер успешно прикреплен к вагону',
                'containers' => $wagonStock->containers
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json([
                'message' => $exception->getMessage() . " | Строка ошибки: " . $exception->getLine() . " Файл: " . $exception->getFile()
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    private function validateWagonCapacity($existingTypes, $newType)
    {
        $count20 = count(array_filter($existingTypes, fn($v) => $v === 20));
        $count40 = count(array_filter($existingTypes, fn($v) => $v === 40));

        if ($newType === 20) {
            if ($count40 === 2) throw new \Exception('Нельзя добавить 20т к двум 40т контейнерам');
            if ($count40 === 1 && $count20 >= 2) throw new \Exception('В вагон с 40т можно добавить максимум два 20т контейнера');
            if ($count20 >= 4) throw new \Exception('Вагон уже содержит 4 контейнера по 20т');
        }

        if ($newType === 40) {
            if ($count40 >= 2) throw new \Exception('Вагон уже содержит два 40т контейнера');
            if ($count40 === 1 && $count20 > 0) throw new \Exception('Во вагон с 40т контейнером можно добавить максимум два 20т');
            if ($count20 > 2) throw new \Exception('Нельзя добавить 40т контейнер в вагон с более чем двумя 20т контейнерами');
        }
    }

    public function getWagonEmployees(): \Illuminate\Http\JsonResponse
    {
        $employees = User:: where('users.id', '!=', Auth::id())
            ->whereIn('users.company_id', [31, 30])
            ->selectRaw('users.*, positions.title as position_name')
            ->selectRaw('(SELECT users_histories.status FROM users_histories WHERE users_histories.user_id=users.id ORDER BY users_histories.id DESC LIMIT 1) as status')
            ->join('positions', 'positions.id', 'users.position_id')
            ->orderBy('id', 'DESC')
            ->orderBy('users.full_name', 'ASC')
            ->having('status', '=', 'works')
            ->get();
        return response()->json($employees, 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function updateWagonShifts(Request $request): \Illuminate\Http\JsonResponse
    {
        $userIds = json_decode($request->input('userIds'), true);
        $userId = Auth::id();

        session()->put("shifts.user_$userId", $userIds);

        return response()->json([
            'message' => 'Смена успешно сохранена в сессию',
            'data' => session()->get("shifts.user_$userId")
        ]);
    }

    public function generateAndDownloadReport(Request $request): string
    {
        return $this->wagonService->generateReport($request->all());
    }
}
