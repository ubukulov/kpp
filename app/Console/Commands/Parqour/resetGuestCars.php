<?php

namespace App\Console\Commands\Parqour;

use App\Models\WhiteCarList;
use Illuminate\Console\Command;
use PARQOUR;

class resetGuestCars extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parqour:resetGuestCars';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Команда обнуляет все гостевые машины.';

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
        $whiteCarLists = WhiteCarList::where(['white_car_lists.pass_type' => 3])
            ->get();
        if($whiteCarLists->isNotEmpty()){
            foreach ($whiteCarLists as $whiteCarList) {
                // делаем запрос на удаление из системы Parqour
                PARQOUR::removeFromWhiteList($whiteCarList->gov_number);

                // удаляем из нашей системы
                $whiteCarList->delete();
            }
        }
    }
}
