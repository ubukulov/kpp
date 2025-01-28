<?php

namespace App\Console\Commands\Technique;

use App\Models\Company;
use App\Models\TechniqueLog;
use App\Models\TechniqueStock;
use Illuminate\Console\Command;

class TechniqueInvent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'technique:invent';

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
        $arr = [
            "SALKA9B96RA237212",
            "SALKABB97RA244533",
            "LUYDB2G37RA008410",
            "LUYDB2G3XRA007980",
            "LUYDB2G36RA008396",
            "LUYJB2G28RA007924",
            "LUYJB2G25RA008612",
            "LUYDB2G31RA008418",
            "LUYJB2G25RA009064",
            "LUYJA2G25RA009326",
            "LUYJA2G29RA009328",
            "LUYJA2G27RA009327",
            "LUYJA2G26RA009349",
            "LUYJA2G29RA009345",
            "LUYJB2G2XRA009075",
            "LUYJA2G24RA009334",
            "LUYJA2G26RA009335",
            "LUYJA2G27RA009330",
            "LUYJB2G26RA011177",
            "LUYJB2G28RA011181",
            "LUYJB2G24RA011341",
            "LUYJB2G2XRA011182",
            "LUYJB2G28RA011178",
            "LUYJB2G21RA011183",
            "LUYJB2G25RA011347",
            "LUYJB2G25RA011350",
            "LUYJB2G21RA011359",
            "LUYJB2G28RA011360",
            "LUYJB2G24RA011369",
            "LUYJB2G20RA011367",
            "LUYJB2G27RA011351",
            "LUYJB2G29RA011352",
            "LUYJB2G2XRA011344",
            "LUYJB2G23RA011363",
            "LUYJB2G20RA011353",
            "LUYJB2G22RA011371",
            "LUYJB2G29RA011349",
            "LUYJB2G28RA011343",
            "LUYJB2G21RA011362",
            "LUYJB2G27RA011348",
            "LUYJA2G22RA011695",
            "LUYJA2G24RA011679",
            "LUYJA2G22RA011373",
            "LUYJA2G26RA011683",
            "LUYJA2G28RA011376",
            "LUYJB2G20RA011109",
            "LUYJB2G2XRA011117",
            "LUYJB2G22RA011144",
            "LUYJB2G23RA011119",
            "LUYJB2G28RA011102",
            "LUYJB2G25RA011140",
            "LUYJB2G20RA011126",
            "LUYJB2G2XRA011134",
            "LUYJB2G27RA011110",
            "LUYJB2G2XRA011103",
            "LUYJB2G24RA011131",
            "LUYJB2G20RA011143"
        ];

        foreach($arr as $vin_code) {
            $technique_stock = TechniqueStock::where(['vin_code' => $vin_code])->first();
            if($technique_stock) {
                $technique_place = $technique_stock->technique_place;
                $company = Company::findOrFail($technique_stock->company_id);
                TechniqueLog::create([
                    'user_id' => 116, 'technique_task_id' => $technique_stock->technique_task_id, 'owner' => $company->full_company_name,
                    'technique_type' => $technique_stock->technique_type->name, 'mark' => $technique_stock->mark, 'vin_code' => $technique_stock->vin_code,
                    'operation_type' => 'canceled', 'address_from' => $technique_place->name, 'address_to' => $technique_place->name,
                    'comment' => 'По инвенту (31.08.2024) удален'
                ]);

                $technique_stock->delete();

                $this->info($vin_code . " deleted successfully");
            } else {
                $this->info($vin_code . " not found");
            }
        }
    }
}
