<?php

namespace App\Console\Commands\Astel;

use Illuminate\Console\Command;
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
    protected $description = 'Обновляет ASTEL токен (действует 24 часа)';

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
        $token = ASTEL::refreshToken();

        if ($token) {
            $this->info("New token successfully saved.");
            $this->line("Token: " . $token);
        } else {
            $this->error("Error when updating token.");
        }
    }
}
