<?php

namespace App\Console\Commands;

use App\Models\WclCompany;
use Illuminate\Console\Command;
use App\Models\WhiteCarList;
use App\Models\WhiteCarLog;
use Illuminate\Support\Facades\DB;

class WCL extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wcl:update';

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
        /*$path_to_file = 'files/92.xlsx';
        $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify(public_path($path_to_file));
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
        $spreadsheet = $reader->load(public_path($path_to_file));
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        foreach ($sheetData as $key=>$arr) {
            $full_name = $arr['A'];
            $position = $arr['B'];
            $mark_car = $arr['D'];
            $gov_number = str_replace(" ", "", $arr['C']);
            $gov_number = str_replace("/", "", $gov_number);
            $company_id = 78;
            $wcl = WhiteCarList::where(['gov_number' => $gov_number])->first();
            if (!$wcl) {
                DB::beginTransaction();
                try {
                    $data['status'] = 'ok';
                    $data['kpp_name'] = 'kpp7';
                    $data['company_id'] = $company_id;
                    $data['gov_number'] = $gov_number;
                    $data['mark_car'] = $mark_car;
                    $data['full_name'] = $full_name;
                    $data['position'] = $position;

                    $white_car_list = WhiteCarList::create($data);

                    WhiteCarLog::create([
                        'wcl_id' => $white_car_list->id, 'user_id' => 1, 'company_id' => $white_car_list->company_id,
                        'gov_number' => $white_car_list->gov_number, 'status' => $white_car_list->status
                    ]);

                    DB::commit();
                } catch (\Exception $exception) {
                    DB::rollBack();
                    abort(500, "$exception");
                }
            }
        }*/

        WhiteCarList::chunk(50, function($wcl){
            foreach($wcl as $item) {
                WclCompany::create([
                    'wcl_id' => $item->id, 'company_id' => $item->company_id
                ]);
            }
        });
    }
}
