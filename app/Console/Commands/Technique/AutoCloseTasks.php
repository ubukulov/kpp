<?php

namespace App\Console\Commands\Technique;

use App\Models\SpineCode;
use App\Models\TechniqueLog;
use App\Models\TechniqueStock;
use App\Models\TechniqueTask;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AutoCloseTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'technique:auto-close-tasks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Закрывает заявки автоматическое (выдачи). Удаляем из стока те записи на которых оформили корешок.
    Остальные записи возвращается в сток.
    ';

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
            $technique_tasks = TechniqueTask::where(['status' => 'open', 'task_type' => 'ship'])->get();

            foreach($technique_tasks as $technique_task) {
                $technique_stocks = $technique_task->stocks;
                foreach($technique_stocks as $technique_stock) {
                    if ($technique_stock->status == 'shipped') {
                        $spine_code = SpineCode::where(['vin_code' => $technique_stock->vin_code])->first();
                        if(!$spine_code) {
                            $technique_stock->status = 'received';
                            $technique_stock->save();
                        }
                    }
                }

                $technique_task->status = 'closed';
                $technique_task->save();
            }

            /*$technique_stocks = TechniqueStock::where(['status' => 'exit_pass'])->get();
            foreach ($technique_stocks as $technique_stock) {
                $company = $technique_stock->company;
                $tech_place = $technique_stock->technique_place;

                $spine_code = SpineCode::where(['vin_code' => $technique_stock->vin_code])->first();
                if($spine_code){
                    $spine = $spine_code->spine;
                    $spine_number = $spine->spine_number;
                } else {
                    $spine_number = "";
                }

                TechniqueLog::create([
                    'user_id' => 116, 'technique_task_id' => $technique_stock->technique_task_id, 'owner' => $company->full_company_name,
                    'technique_type' => $technique_stock->technique_type->name, 'mark' => $technique_stock->mark, 'vin_code' => $technique_stock->vin_code,
                    'operation_type' => 'completed', 'address_from' => $tech_place->name, 'address_to' => 'completed', 'spine_number' => $spine_number,
                ]);

                $technique_stock->delete();
            }*/

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
        }
    }
}
