<?php

namespace App\Console\Commands\Bitrix24;

use App\Classes\Bitrix24;
use App\Models\Service\ServiceRequest;
use App\Models\Status;
use Carbon\Carbon;
use Illuminate\Console\Command;

class syncStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bitrix:sync-statuses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bitrix24 автоматическое получает текущий статус';

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
        $bitrix24 = new Bitrix24();
        $service_requests = ServiceRequest::/*where('status_id', '<', 3)->get()*/all();
        foreach ($service_requests as $service_request) {
            $result = $bitrix24->getStage($service_request->bitrix24_id);
            $carbon = Carbon::parse($result['ufCrm12_1757939245155']);
            $statusId = Status::getStatusId($result['stageId']);
            if(true){
                $service_request->status_id = $statusId;
                $service_request->decision = $result['ufCrm12_1757939063899'][0] . " (" . $carbon->format('Y-m-d H:i:s') . ")"; // решение заявки
                $service_request->save();
            }
        }
    }
}
