<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ImportUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:users';

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
        $path_to_file = 'files/modx2.xlsx';
        $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify(public_path($path_to_file));
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
        $spreadsheet = $reader->load(public_path($path_to_file));
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        foreach ($sheetData as $key=>$arr) {
            if ($key == 1) continue;
            $iin = trim($arr['B']);
            $full_name = $arr['C'];
            $email = (empty($arr['D'])) ? null : $arr['D'];
            $phone = $arr['E'];
            $company_id = $arr['F'];
            $position_id = $arr['G'];

            if (empty($phone)) {
                $phone = '-';
            } else {
                $phone = str_replace(" ", "", $arr['E']);
                $phone = (substr($phone,0,1) == '8') ? '+7'.substr($phone,1) : '+'.$phone;
            }

            User::create([
                'company_id' => $company_id, 'position_id' => $position_id, 'full_name' => $full_name,
                'phone' => $phone, 'iin' => $iin, 'email' => $email
            ]);
        }
    }
}
