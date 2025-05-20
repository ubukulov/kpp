<?php

namespace App\Console\Commands\Astel;

use Illuminate\Console\Command;
use Cache;
use ASTEL;

class RefreshAstelToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'astel:refresh-token';

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
        $response = ASTEL::getToken();

        if($response->getStatusCode() == 200) {
            $responseContent = json_decode($response->getContent());
            Cache::put('astel_token', $responseContent->token);
            $this->info("Token: " . Cache::get('astel_token'));
        }
    }
}
