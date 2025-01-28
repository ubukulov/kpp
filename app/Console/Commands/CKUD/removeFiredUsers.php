<?php

namespace App\Console\Commands\CKUD;

use App\Models\User;
use Illuminate\Console\Command;
use Cache;
use CKUD;

class removeFiredUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ckud:remove-fired-users';

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
        $employees = User::orderBy('id', 'DESC')
            ->selectRaw('users.*, companies.short_ru_name, positions.title as position_name, departments.title as department_name')
            ->selectRaw('(SELECT users_histories.status FROM users_histories WHERE users_histories.user_id=users.id ORDER BY users_histories.id DESC LIMIT 1) as status')
            ->join('companies', 'companies.id', 'users.company_id')
            ->join('positions', 'positions.id', 'users.position_id')
            ->leftJoin('departments', 'departments.id', 'users.department_id')
            ->get();
        foreach ($employees as $employee) {
            if($employee->status != 'fired') {
                continue;
            } else {
                if(array_key_exists($employee->id, Cache::get('ckud_users'))) {
                    $responseCode = CKUD::removeEmployee(Cache::get('ckud_users')[$employee->id]);
                    if($responseCode == 200) {
                        $this->info($employee->id. " removed successfully.");
                    }
                }
            }
        }
        $this->info('The process finished.');
    }
}
