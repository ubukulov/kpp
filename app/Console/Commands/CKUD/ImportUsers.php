<?php

namespace App\Console\Commands\CKUD;

use App\Models\User;
use Illuminate\Console\Command;
use CKUD;

class ImportUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ckud:import-users';

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
        $ckud_users_numbers = [];
        $ckud_users = CKUD::getEmployees();
        if($ckud_users->result == 0) {
            foreach($ckud_users->data as $datum) {
                if(!array_key_exists($datum->Number, $ckud_users_numbers)) {
                    $ckud_users_numbers[$datum->Number] = $datum->Number;
                }
            }
        }

        $users = User::whereRaw('LENGTH(users.uuid) = 7')
                ->selectRaw('users.*, companies.full_company_name, positions.title as position_name')
                ->selectRaw("CONCAT('https://kpp.dlg.kz:8900', REGEXP_REPLACE(users.image, 'f', 'l')) as photo_http")
                ->join('companies', 'companies.id', 'users.company_id')
                ->join('positions', 'positions.id', 'users.position_id')
                ->get();
        foreach($users as $user) {
            if(!array_key_exists($user->id, $ckud_users_numbers)) {
                $data = [
                    'Comment' => $user->position_name,
                    'employeeGroupID' => 'f6e6250b-69f4-4f4a-9c4b-2bc357645d6b',
                    'Number' => $user->id,
                    'KeyNumber' => $user->uuid,
                    'ResidentialAddress' => $user->full_company_name,
                    'photo_http' => $user->photo_http,
                ];

                $arr = explode(" ", $user->full_name);
                $LastName = (array_key_exists(0, $arr)) ? $arr[0] : '';
                $FirstName = (array_key_exists(1, $arr)) ? $arr[1] : '';
                $SecondName = (array_key_exists(2, $arr)) ? $arr[2] : '';
                $data['LastName'] = $LastName;
                $data['FirstName'] = $FirstName;
                $data['SecondName'] = $SecondName;
                if(CKUD::addEmployee($data) == 200) {
                    $this->info($user->full_name . " added successfully.");
                } else {
                    $this->info($user->full_name . " added not successfully.");
                }
            }
        }

        $this->info('The process is finished.');
    }
}
