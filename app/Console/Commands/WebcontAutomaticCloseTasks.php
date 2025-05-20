<?php

namespace App\Console\Commands;

use App\Models\ContainerLog;
use App\Models\ContainerStock;
use App\Models\ContainerTask;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class WebcontAutomaticCloseTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webcont:auto-close-tasks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Команда каждый день 23:55 время получает все открытие заявки по выдачи контейнера по авто, и аннулирует заявку на выдачу.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        DB::beginTransaction();
        try {
            $container_tasks = ContainerTask::where([
                'status' => 'open', 'task_type' => 'ship', 'trans_type' => 'auto'
            ])->get();

            foreach($container_tasks as $container_task){
                $container_stocks = ContainerStock::where(['container_task_id' => $container_task->id])->get();
                foreach($container_stocks as $container_stock) {
                    $container_stock->status = 'received';
                    $container_stock->container_task_id = null;
                    $container_stock->save();

                    $container = $container_stock->container;
                    $container_address = $container_stock->container_address;

                    $cs = $container_stock->attributesToArray();
                    $cs['user_id'] = 116;
                    $cs['container_number'] = $container->number;
                    $cs['operation_type'] = 'canceled';
                    $cs['address_from'] = $container_address->name;
                    $cs['address_to'] = $container_address->name;
                    $cs['action_type'] = 'canceled';
                    $cs['note'] = 'Аннулирован по автомату!';

                    // Зафиксируем в лог
                    ContainerLog::create($cs);
                }

                $container_task->status = 'closed';
                $container_task->save();
            }

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            echo $exception->getMessage();
        }
    }
}
