<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Permit;

class RefreshDatePermit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rdp:update';

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
        $time1 = date('Y-m-d H:i:s');
        Permit::chunk(100, function($permits){
            foreach($permits as $permit){
				// Дата заезда
				if(!is_null($permit->date_in)){
					$permit->date_in = date("Y-m-d H:i:s", strtotime($permit->date_in));
					$permit->save();
				}

				// Дата выезда
				if(!is_null($permit->date_out)){
					$permit->date_out = date("Y-m-d H:i:s", strtotime($permit->date_out));
					$permit->save();
				}
			}
		});
        $time2 = date('Y-m-d H:i:s');
        $sec = abs(strtotime($time2) - strtotime($time1));
		$this->info("Operation completed in ".$sec." seconds");
    }
}
