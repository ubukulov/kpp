<?php

namespace App\Console\Commands;

use App\Models\TechniqueLog;
use App\Models\TechniqueTask;
use Illuminate\Console\Command;

class WebcontAutomaticCloseTechniqueTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webcont:auto-close-technique-tasks';

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
        $technique_tasks = TechniqueTask::where(['status' => 'open', 'task_type' => 'ship', 'trans_type' => 'auto'])->get();

        foreach($technique_tasks as $technique_task) {
            $technique_stocks = $technique_task->stocks;
            foreach($technique_stocks as $technique_stock) {

                if($technique_stock->status == 'shipped') {
                    $tech_place = $technique_stock->technique_place;
                    TechniqueLog::create([
                        'user_id' => 116, 'technique_task_id' => $technique_task->id, 'owner' => $technique_stock->owner,
                        'technique_type' => $technique_stock->technique_type->name, 'mark' => $technique_stock->mark, 'vin_code' => $technique_stock->vin_code,
                        'operation_type' => 'completed', 'address_from' => $tech_place->name, 'address_to' => 'completed'
                    ]);

                    $technique_stock->delete();
                } else {
                    $technique_stock->status = 'received';
                    $technique_stock->technique_task_id = null;
                    $technique_stock->save();
                }

            }

            $technique_task->status = 'closed';
            $technique_task->save();
        }
    }
}
