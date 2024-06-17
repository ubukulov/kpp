<?php

namespace App\Console\Commands\Technique;

use App\Models\Company;
use App\Models\TechniqueLog;
use App\Models\TechniqueStock;
use Illuminate\Console\Command;

class refresh extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'technique:refresh';

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
        $technique_stocks = TechniqueStock::all();
        foreach ($technique_stocks as $technique_stock) {
            if($technique_stock->technique_task_id == 366 || $technique_stock->technique_task_id == 367) {
                continue;
            } else {
                $data = $technique_stock->attributesToArray();
                $data['user_id'] = 116;
                $technique_place = $technique_stock->technique_place;
                $technique_type = $technique_stock->technique_type;
                $data['technique_task_id'] = (is_null($data['technique_task_id'])) ? 0 : $data['technique_task_id'];
                $data['operation_type'] = 'canceled';
                $data['address_from'] = $technique_place->name;
                $data['address_to'] = $technique_place->name;
                $data['technique_type'] = $technique_type->name;

                if(is_null($data['company_id'])) {
                    $data['owner'] = null;
                } else {
                    $company = Company::findOrFail($data['company_id']);
                    $data['owner'] = $company->short_en_name;
                }

                TechniqueLog::create($data);

                $technique_stock->delete();
            }
        }
    }
}
