<?php

namespace App\Console\Commands\Technique;

use App\Models\TechniqueLog;
use App\Models\TechniqueStock;
use Illuminate\Console\Command;

class GenerateReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'technique:generate-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Каждый день в 08 утра на адресатов отправляется отчет о завезенных и вывезенных авто';

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
        $from_date = date('Y-m-d', strtotime(' -1 day')) . ' 08:00:00';
        $to_date = date('Y-m-d') . ' 08:00:00';
        $technique_logs = TechniqueLog::where('technique_logs.created_at', '>=', $from_date)
            ->where('technique_logs.created_at', '<=', $to_date)
            ->whereIn('technique_logs.operation_type', ['received', 'completed'])
            ->selectRaw('users.id as user_id,users.full_name,technique_logs.*')
            ->join('users', 'users.id', '=', 'technique_logs.user_id')
            ->get();

        $technique_stocks = TechniqueStock::where('status', '!=', 'exit_pass')
            ->where('status', '!=', 'incoming')
            ->get();

        $beautymail = app()->make(\Snowfire\Beautymail\Beautymail::class);
        $beautymail->send('emails.technique.report', [
            'data' => $technique_logs,
            'from_date' => $from_date,
            'to_date' => $to_date,
            'stocks' => $technique_stocks->count(),
        ], function($message)
        {
            $message
                ->from('webcont@dlg.kz')
                ->to('webcont.rep@dlg.kz')
                //->to('kairat.ubukulov@htl.kz')
                ->subject('ILC. Информация завезенных и вывезенных авто');
        });
    }
}
