<?php

namespace App\Console\Commands\KPP;

use App\Models\Permit;
use Illuminate\Console\Command;

class unCompletedPermits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permit:uncompleted';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Отправляет на почту инфо о незакрытых пропусков на КПП';

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
        $unCompletedPermits = Permit::getUnCompletedPermits();
        $beautymail = app()->make(\Snowfire\Beautymail\Beautymail::class);
        $beautymail->send('emails.kpp', [
            'data' => $unCompletedPermits,
        ], function($message)
        {
            $message
                ->from('webcont@dlg.kz')
                //->to('kairat.ubukulov@htl.kz')
                ->to('nadamu.kpp@dlg.kz')
                ->subject('ВНИМАНИЕ: НЕЗАКРЫТЫЕ ПРОПУСКИ');
        });
    }
}
