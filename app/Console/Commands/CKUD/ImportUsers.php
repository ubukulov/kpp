<?php

namespace App\Console\Commands\CKUD;

use App\Models\User;
use Illuminate\Console\Command;
use CKUD;
use Cache;

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
        Cache::forget('ckud_users');
        $ckud_users_numbers = Cache::get('ckud_users') ?? [];

        $users = User::whereRaw('LENGTH(users.uuid) = 7')/*->where('users.company_id', 13)*/
                ->selectRaw('users.*, companies.full_company_name, companies.type_company, positions.title as position_name, companies.ckud_group_id')
                ->selectRaw("CONCAT('https://kpp.dlg.kz:8900/', REGEXP_REPLACE(users.image, 'f', 'l')) as photo_http")
                ->selectRaw('(SELECT users_histories.status FROM users_histories WHERE users_histories.user_id=users.id ORDER BY users_histories.id DESC LIMIT 1) as status')
                ->join('companies', 'companies.id', 'users.company_id')
                ->join('positions', 'positions.id', 'users.position_id')
                ->get();

        foreach($users as $user) {
            if($user->status == 'fired') {
                continue;
            }

            if(!array_key_exists($user->id, $ckud_users_numbers)) {
                /*switch ($user->type_company) {
                    case "resident":
                        $employeeGroupID = 'e6abf420-eae2-4d6a-a203-1f0b1e106026';
                        break;
                    case "rent":
                        $employeeGroupID = '40a2b96e-4c7b-462d-9f71-19fd2da77abf';
                        break;
                    default:
                        $employeeGroupID = 'f6e6250b-69f4-4f4a-9c4b-2bc357645d6b';
                        break;
                }*/

                if(!is_null($user->ckud_group_id)) {
                    $employeeGroupID = $user->ckud_group_id;
                } else {
                    $employeeGroupID = 'f6e6250b-69f4-4f4a-9c4b-2bc357645d6b';
                }

                $data = [
                    'Comment' => $user->position_name,
                    'employeeGroupID' => $employeeGroupID,
                    'Number' => $user->id,
                    'KeyNumber' => $user->uuid,
                    'ResidentialAddress' => $user->full_company_name,
                    'photo_http' => $user->photo_http,
                ];

                $arr = explode(" ", $user->full_name);
                $LastName   = (array_key_exists(0, $arr)) ? $arr[0] : '';
                $FirstName  = (array_key_exists(1, $arr)) ? $arr[1] : '';
                $SecondName = (array_key_exists(2, $arr)) ? $arr[2] : '';
                $data['LastName'] = $LastName;
                $data['FirstName'] = $FirstName;
                $data['SecondName'] = $SecondName;
                if(CKUD::addEmployee($data) == 200) {
                    $this->info($user->uuid . " added successfully.");
                } else {
                    $this->info($user->uuid . " added not successfully.");
                }
            }
        }

        $this->info('The process is finished.');
    }
}
