<?php

namespace App\Console\Commands;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use CKUD;

class RefreshUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'refresh:users';

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
        /*foreach (range(1, 25) as $number) {
            $data['password'] = (empty($data['password'])) ? null : bcrypt($data['password']);
            $data['status'] = 'works';
            $user = User::create([
                'company_id' => 31, 'position_id' => 224, 'full_name' => 'Гость №'.$number,
            ]);

            // если у пользователя не задан uuid, то его генерируем и сохраняем
            $user->uuid = $user->generateUniqueRandomNumber();
            $user->save();

            // Зафиксируем статусы
            if ($user->hasWorkingStatus()) {
                if ($user->getWorkingStatus()->status != $data['status']) {
                    $user->createUserHistory($user, $data);
                }
            } else {
                $user->createUserHistory($user, $data);
            }

            // присвоение ролей к пользователю
            if(!empty($data['roles'])) {
                foreach($data['roles'] as $item) {
                    $role = Role::findOrFail($item);
                    $user->roles()->attach($role);
                }
            }

            // дать разрешение к пользователю
            if (!empty($data['permissions'])) {
                foreach($data['permissions'] as $value) {
                    $permission = Permission::findOrFail($value);
                    $user->permissions()->attach($permission);
                }
            }

            $data = [
                'Comment' => $user->position->title,
                'employeeGroupID' => 'f2b5aa33-e5ec-4c19-9513-a48ab2db3fb1',
                'Number' => $user->id,
                'KeyNumber' => $user->uuid,
                'ResidentialAddress' => $user->company->full_company_name,
                'photo_http' => 'https://campussafetyconference.com/wp-content/uploads/et_temp/iStock-476085198-113597_1080x675.jpg',
            ];

            $data['LastName'] = 'Гость '.$number;
            $data['FirstName'] = 'Гость '.$number;
            $data['SecondName'] = 'Гость '.$number;

            CKUD::addEmployee($data);
        }*/


        /*User::chunk(100, function($users){
            foreach($users as $user) {
                if($user->id == 438) {
                    dd($user->phone);
                }
            }
        });*/
    }
}
