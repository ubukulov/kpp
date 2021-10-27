<?php

namespace App\Console\Commands;

use App\Models\Container;
use App\Models\ContainerStock;
use App\Models\ContainerTask;
use App\Models\ImportLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ContainerTaskRefresh extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'container-task:refresh';

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
        ContainerTask::chunk(50, function($container_tasks){
            foreach($container_tasks as $container_task) {
                if ($container_task->status == 'closed') {
                    if ($container_task->task_type == 'receive') {
                        DB::update("UPDATE import_logs SET state='posted' WHERE container_task_id='$container_task->id'");
                    } else {
                        DB::update("UPDATE import_logs SET state='issued' WHERE container_task_id='$container_task->id'");
                    }
                }

                if ($container_task->status == 'open') {
                    if ($container_task->task_type == 'receive') {
                        foreach($container_task->import_logs as $import_log) {
                            $container = Container::whereNumber($import_log->container_number)->first();
                            if ($container) {
                                $container_stock = ContainerStock::where(['container_id' => $container->id])->first();
                                if ($container_stock && $container_stock->status == 'received') {
                                    $import_log = ImportLog::where(['container_task_id' => $container_task->id, 'container_number' => $container->number])->first();
                                    $import_log->state = 'posted';
                                    $import_log->save();
                                } elseif ($container_stock && $container_stock->status == 'incoming') {
                                    $import_log = ImportLog::where(['container_task_id' => $container_task->id, 'container_number' => $container->number])->first();
                                    $import_log->state = 'not_posted';
                                    $import_log->save();
                                }
                            }
                        }
                    }

                    if ($container_task->task_type == 'ship' && $container_task->trans_type == 'auto') {
                        foreach($container_task->import_logs as $import_log) {
                            $container = Container::whereNumber($import_log->container_number)->first();
                            if ($container) {
                                $container_stock = ContainerStock::where(['container_id' => $container->id])->first();
                                if ($container_stock && $container_stock->status == 'shipped') {
                                    $import_log = ImportLog::where(['container_task_id' => $container_task->id, 'container_number' => $container->number])->first();
                                    $import_log->state = 'issued';
                                    $import_log->save();

                                    // Удаление из стока
                                    ContainerTask::complete_part($container_task->id, $container->id);
                                } elseif ($container_stock && $container_stock->status == 'in_order') {
                                    $import_log = ImportLog::where(['container_task_id' => $container_task->id, 'container_number' => $container->number])->first();
                                    $import_log->state = 'not_selected';
                                    $import_log->save();
                                }
                            }
                        }

                        if ($container_task->allowCloseThisTask()) {
                            $container_task->status = 'closed';
                            $container_task->save();
                        }
                    }

                    if ($container_task->task_type == 'ship' && $container_task->trans_type == 'train') {
                        foreach($container_task->import_logs as $import_log) {
                            $container = Container::whereNumber($import_log->container_number)->first();
                            if ($container) {
                                $container_stock = ContainerStock::where(['container_id' => $container->id])->first();
                                if ($container_stock && $container_stock->status == 'shipped') {
                                    $import_log = ImportLog::where(['container_task_id' => $container_task->id, 'container_number' => $container->number])->first();
                                    $import_log->state = 'selected';
                                    $import_log->save();
                                } elseif ($container_stock && $container_stock->status == 'in_order') {
                                    $import_log = ImportLog::where(['container_task_id' => $container_task->id, 'container_number' => $container->number])->first();
                                    $import_log->state = 'not_selected';
                                    $import_log->save();
                                }
                            }
                        }
                    }
                }
            }
        });
    }
}
