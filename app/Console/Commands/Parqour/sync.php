<?php

namespace App\Console\Commands\Parqour;

use App\Models\WclCompany;
use Illuminate\Console\Command;
use PARQOUR;

class sync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parqour:sync';

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
        $wcls = WclCompany::where(['wcl_companies.status' => 'ok'])
                ->selectRaw('white_car_lists.*, companies.short_en_name')
                ->join('companies', 'companies.id', '=', 'wcl_companies.company_id')
                ->join('white_car_lists', 'white_car_lists.id', '=', 'wcl_companies.wcl_id')
                //->whereIn('wcl_companies.company_id', [45])
                ->where(['wcl_companies.company_id' => 113])
                //->whereIn('wcl_companies.company_id', [80,151,224,63,56,33])
                ->get();
        foreach($wcls as $wcl){
            $comment = (!is_null($wcl->full_name)) ? $wcl->short_en_name . "|" . $wcl->full_name : $wcl->short_en_name;
            $plateNumber = str_replace(' ', '', $wcl->gov_number);
            $params = [
                'plateNumber' => $plateNumber,
                'comment' => $comment,
                'groupName' => $wcl->short_en_name,
                'fullName' => $wcl->full_name,
            ];
            PARQOUR::addToWhiteList($params);
            $this->info($plateNumber . ' sync is successfully.');
        }

        $this->info('End.');
    }
}
