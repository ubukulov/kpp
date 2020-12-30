<?php

namespace App\Console\Commands;

use App\Car;
use App\Permit;
use Illuminate\Console\Command;

class RefreshCar extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'refresh:car';

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
        Permit::chunk(100, function($permits){
            foreach($permits as $permit){
                $tex_number = str_replace(" ", "", $permit->tex_number);
                $tex_number = trim(strtoupper($tex_number));
                $gov_number = trim(mb_strtoupper(str_replace(" ", "", $permit->gov_number)));
                if(!Car::exists($tex_number)) {
                    $mark_car = trim(mb_strtoupper($permit->mark_car));
                    $body_type_id = $permit->body_type_id;
                    $cat_tc_id = $permit->cat_tc_id;
                    $pr_number = trim(mb_strtoupper(str_replace(" ", "", $permit->pr_number)));
                    Car::create([
                        'tex_number' => $tex_number, 'gov_number' => $gov_number, 'mark_car' => $mark_car,
                        'body_type_id' => $body_type_id, 'cat_tc_id' => $cat_tc_id, 'pr_number' => $pr_number
                    ]);
                    $this->info("Car: ".$gov_number." with ".$tex_number." add to table drivers");
                }
            }
        });
        $this->info('Operation completed');
    }
}
