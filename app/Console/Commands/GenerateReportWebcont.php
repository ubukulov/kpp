<?php

namespace App\Console\Commands;

use App\Models\ContainerLog;
use App\Models\ContainerStock;
use App\Models\ContainerTask;
use App\Models\Permit;
use App\Models\Technique;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateReportWebcont extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:webcont';

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
        $date_time = date('H');
        $notClosedTasks = ContainerTask::selectRaw("COUNT(*) as cnt")
            ->whereRaw('LENGTH(container_ids) > 2 and status="open"')
            ->get();

        if($date_time == '20') {
            $from = date('Y-m-d') . ' 08:05:00';
            $to = date('Y-m-d') . ' 20:00:00';
            $notClosedTasksToday = ContainerTask::selectRaw("COUNT(*) as cnt")
                ->whereDate('created_at', Carbon::today())
                //->whereBetween('created_at', [$from, $to])
                ->whereRaw('LENGTH(container_ids) > 2 AND status="open"')
                ->get();
        } else {
            $from = date('Y-m-d', strtotime(' -1 day')) . ' 20:00:00';
            $to = date('Y-m-d') . ' 08:00:00';
            $notClosedTasksToday = ContainerTask::selectRaw("COUNT(*) as cnt")
                ->whereDate('created_at', Carbon::today())
                //->whereBetween('created_at', [$from, $to])
                ->whereRaw('LENGTH(container_ids) > 2 AND status="open"')
                ->get();
        }


        $countContainersDamuIn = ContainerStock::selectRaw("COUNT(*) as cnt")
            ->where(['status' => 'incoming'])
            ->get();
        $countContainersDamuOut = ContainerStock::selectRaw("COUNT(*) as cnt")
            ->where(['status' => 'shipped'])
            ->get();
        $countContainersBuffer = ContainerStock::selectRaw("COUNT(*) as cnt")
            ->where(['container_address_id' => 3])
            ->get();

        $getContainersByZone = ContainerStock::join('container_address', 'container_address.id', 'container_stocks.container_address_id')
                    ->selectRaw("container_address.title,container_address.zone, count(*) as cnt")
                    ->groupBy('container_address.zone')
                    ->get();

        if($date_time == '20') {
            $from = date('Y-m-d') . ' 08:05:00';
            $to = date('Y-m-d') . ' 20:00:00';
            $getStatsByUserAndTechniques = ContainerLog::whereBetween('container_logs.created_at', [$from, $to])/*whereDate('container_logs.created_at', Carbon::today())*/
                ->selectRaw('users.id as user_id,users.full_name,container_logs.technique_id,techniques.name as technique_name')
                ->join('users', 'users.id', '=', 'container_logs.user_id')
                ->join('techniques', 'techniques.id', '=', 'container_logs.technique_id')
                ->get();
        } else {
            $from = date('Y-m-d', strtotime(' -1 day')) . ' 20:00:00';
            $to = date('Y-m-d') . ' 08:00:00';
            $getStatsByUserAndTechniques = ContainerLog::whereBetween('container_logs.created_at', [$from, $to])/*whereDate('container_logs.created_at', Carbon::today())*/
                ->selectRaw('users.id as user_id,users.full_name,container_logs.technique_id,techniques.name as technique_name')
                ->join('users', 'users.id', '=', 'container_logs.user_id')
                ->join('techniques', 'techniques.id', '=', 'container_logs.technique_id')
                ->get();
        }


        $nct = $notClosedTasks[0]->cnt;
        $nctt = $notClosedTasksToday[0]->cnt;
        $ccdi = $countContainersDamuIn[0]->cnt;
        $ccdo = $countContainersDamuOut[0]->cnt;
        $ccb = $countContainersBuffer[0]->cnt;

        $techniques = Technique::all();
        $stats = [];
        foreach($getStatsByUserAndTechniques as $item) {
            if(array_key_exists($item->user_id, $stats)) {
                if(array_key_exists($item->technique_id, $stats[$item->user_id]['techs'])) {
                    $stats[$item->user_id]['techs'][$item->technique_id]['cnt'] += 1;
                }
            } else {
                $stats[$item->user_id]['full_name'] = $item->full_name;
                foreach($techniques as $technique) {
                    $stats[$item->user_id]['techs'][$technique->id]['technique_name'] = $technique->name;
                    if($technique->id == $item->technique_id) {
                        $stats[$item->user_id]['techs'][$item->technique_id]['cnt'] = 1;
                    } else{
                        $stats[$item->user_id]['techs'][$technique->id]['cnt'] = 0;
                    }
                }
            }
        }

        if($date_time == '20') {
            $from = date('Y-m-d') . ' 08:05:00';
            $to = date('Y-m-d') . ' 20:00:00';
            $countPermitsLoading   = Permit::where(['status' => 'printed', 'operation_type' => 1, 'kpp_name' => 'kpp4'])
                //->whereDate('date_in', '=', Carbon::today()->toDateString())
                ->whereBetween('date_in', [$from, $to])
                ->get()
                ->count();
            $countPermitsUnLoading = Permit::where(['status' => 'printed', 'operation_type' => 2, 'kpp_name' => 'kpp4'])
                //->whereDate('date_in', '=', Carbon::today()->toDateString())
                ->whereBetween('date_in', [$from, $to])
                ->get()
                ->count();
        } else {
            $from = date('Y-m-d', strtotime(' -1 day')) . ' 20:00:00';
            $to = date('Y-m-d') . ' 08:00:00';
            $countPermitsLoading   = Permit::where(['status' => 'printed', 'operation_type' => 1, 'kpp_name' => 'kpp4'])
                //->whereDate('date_in', '=', Carbon::today()->toDateString())
                ->whereBetween('date_in', [$from, $to])
                ->get()
                ->count();
            $countPermitsUnLoading = Permit::where(['status' => 'printed', 'operation_type' => 2, 'kpp_name' => 'kpp4'])
                //->whereDate('date_in', '=', Carbon::today()->toDateString())
                ->whereBetween('date_in', [$from, $to])
                ->get()
                ->count();
        }

        $permit = [
            'countPermitsLoading' => $countPermitsLoading, 'countPermitsUnLoading' => $countPermitsUnLoading
        ];

        $beautymail = app()->make(\Snowfire\Beautymail\Beautymail::class);
        $beautymail->send('emails.webcont', [
            'getContainersByZone' => $getContainersByZone, 'nct' => $nct, 'nctt' => $nctt, 'ccdi' => $ccdi, 'ccdo' => $ccdo, 'ccb' => $ccb,
            'stats' => $stats, 'techniques' => $techniques, 'permit' => $permit
        ], function($message)
        {
            $message
                ->from('webcont@dlg.kz')
                ->to('webcont.rep@dlg.kz')
                //->to('kairat.ubukulov@htl.kz')
                ->subject('Статистика по WEBCONT!');
        });
    }
}
