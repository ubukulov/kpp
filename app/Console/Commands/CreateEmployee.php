<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Department;
use App\Models\Position;

class CreateEmployee extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:employee';

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
        $csvFileName = "ТОО Beibars Security.txt";
        $csvFile = public_path('files/' . $csvFileName);
        $fp = fopen($csvFile, "rt"); // Открываем файл в режиме чтения
        /*if ($fp) {
            while (!feof($fp)) {
                $line = fgets($fp,999);
                $arr = explode('|', $line);
                $emp = array();
                $emp['company_id'] = 33;
                $emp['full_name'] = $arr[3];
                $emp['phone'] = $arr[4];

                $department_name = trim($arr[1]);
                $department = Department::whereRaw("TRIM(title) = '$department_name'")->first();
                $emp['department_id'] = ($department) ? $department->id : 0;

                $position_name = trim($arr[2]);
                $position = Position::whereRaw("TRIM(title) = '$position_name'")->first();
                $emp['position_id'] = ($position) ? $position->id : 0;

                User::create($emp);
            }
        }*/
    }
}
