<?php

namespace App\Console\Commands;

use App\Models\ContainerLog;
use App\Models\ContainerStock;
use App\Models\ContainerTask;
use Illuminate\Console\Command;

class RestoreStocks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'restore:stocks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        try {
            /*$arrQ = [68355
                ,68356
                ,68357
                ,68358
                ,68359
                ,68360
                ,68361
                ,68362
                ,68451
                ,68452
                ,68453
                ,68454
                ,68494
                ,68679];
            // По стоку
            ContainerStock::chunk(100, function($container_stocks) use ($arrQ){
                foreach($container_stocks as $container_stock) {
                    if(in_array($container_stock->id, $arrQ)) {
                        continue;
                    }

                    $data = $container_stock->attributesToArray();
                    $data['user_id'] = 116;
                    $data['container_number'] = ($container_stock->container) ? $container_stock->container->number : null;
                    $data['operation_type'] = 'canceled';
                    $data['action_type'] = 'canceled';
                    $data['address_from'] = ($container_stock->container_address) ? $container_stock->container_address->name : null;
                    $data['address_to'] = 'Удален по инвенту от '.date('d.m.Y');
                    $data['note'] = 'По инвенту '.date('d.m.Y').' удален из стока';

                    ContainerLog::create($data);

//                    $container_task = $container_stock->container_task;
//                    if($container_task && $container_task->status == 'open') {
//                        $container_task->status = 'closed';
//                        $container_task->save();
//                    }

                    $this->info("Stock $container_stock->id has been ignored.");

                    $container_stock->delete();
                }
            });

            $this->info('Process is finished.');*/

            // По заявкам
            /*ContainerTask::where(['status' => 'open'])->chunk(50, function($container_tasks){
                foreach($container_tasks as $container_task) {
                    $container_stocks = $container_task->container_stocks();
                    if(count($container_stocks) > 0) {
                        foreach($container_stocks as $container_stock) {
                            $data = $container_stock->attributesToArray();
                            $data['user_id'] = 116;
                            $data['container_number'] = ($container_stock->container) ? $container_stock->container->number : null;
                            $data['operation_type'] = 'canceled';
                            $data['action_type'] = 'canceled';
                            $data['address_from'] = ($container_stock->container_address) ? $container_stock->container_address->name : null;
                            $data['address_to'] = 'Удален по инвенту от ' . date('d.m.Y');
                            $data['note'] = "По инвенту ".date('d.m.Y')." удален из стока";
                            ContainerLog::create($data);
                            $container_stock->delete();
                        }

                        $container_task->status = 'ignore';
                        $container_task->save();

                        $this->info("Task $container_task->id has been ignored.");
                    }
                }
            });
            $this->info('Process is finished.');*/
        } catch (\Exception $exception) {
            dd($exception);
        }
    }
}
