<?php

namespace App\Console\Commands;

use App\Driver;
use App\Permit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RefreshDriver extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'refresh:driver';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Перенос данные о водителя в таблицу drivers';

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
        Permit::chunk(100, function($permits){
            foreach($permits as $permit){
                if(!Driver::exists($permit->ud_number)) {
                    $fio = trim(mb_strtoupper($permit->last_name));
                    $phone = trim($permit->phone);
                    $ud_number = $permit->ud_number;
                    $ud_number = trim(mb_strtoupper($ud_number));
                    $ud_number = str_replace(" ", "", $ud_number);
                    Driver::create([
                        'fio' => $fio, 'phone' => $phone, 'ud_number' => $ud_number
                    ]);
                    $this->info("Driver: ".$fio." with ".$ud_number." add to table drivers");
                }
            }
        });
        $this->info('Operation completed');
    }
}
