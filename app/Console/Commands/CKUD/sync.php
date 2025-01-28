<?php

namespace App\Console\Commands\CKUD;

use App\Models\CKUDLogs;
use Illuminate\Console\Command;
use CKUD;

class sync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ckud:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Автоматом получает последние событие и сохраняет в БД';

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
        $lastEventCKUD = CKUD::getLastEvent();
        $lastEvent = CKUDLogs::orderBy('id', 'DESC')->first();
        if($lastEventCKUD['Id'] != $lastEvent->ckud_id) {
            $events = CKUD::getEvents($lastEvent->ckud_id);
            if($events AND count($events['Events']) != 0) {
                foreach($events['Events'] as $event) {
                    $data = $event;
                    $data['ckud_id'] = $event['Id'];
                    CKUDLogs::create($data);
                }
            }
        }
    }
}
