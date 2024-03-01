<?php

namespace App\Console\Commands;

use App\Models\TechniqueLog;
use App\Models\TechniqueStock;
use Illuminate\Console\Command;

class RefreshTechniqueStocks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'refresh:technique-stocks';

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
        $technique_stocks = TechniqueStock::where(['technique_stocks.status' => 'shipped', 'technique_tasks.status' => 'closed'])
            ->selectRaw('technique_stocks.*')
            ->join('technique_tasks', 'technique_tasks.id', 'technique_stocks.technique_task_id')
            ->get();

        foreach($technique_stocks as $technique_stock) {
            $technique_place = $technique_stock->technique_place;
            TechniqueLog::create([
                'user_id' => 116, 'technique_task_id' => $technique_stock->technique_task_id, 'owner' => $technique_stock->owner,
                'technique_type' => $technique_stock->technique_type->name, 'mark' => $technique_stock->mark, 'vin_code' => $technique_stock->vin_code,
                'operation_type' => 'completed', 'address_from' => $technique_place->name, 'address_to' => 'completed'
            ]);

            $technique_stock->delete();
        }
    }
}
