<?php

namespace App\Console\Commands\ANT;

use App\Models\ContainerStock;
use Illuminate\Console\Command;

use ANT;

class StockUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ant:stock-update';

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
        $container_stocks = ContainerStock::orderBy('container_stocks.created_at', 'desc')
                ->selectRaw('container_stocks.*, containers.number as container_number,container_address.title,container_address.zone,container_address.name, containers.container_type')
                ->selectRaw('container_address.row, container_address.place, container_address.floor')
                ->join('containers', 'containers.id', '=', 'container_stocks.container_id')
                ->join('container_address', 'container_address.id', '=', 'container_stocks.container_address_id')
                ->get();

        foreach($container_stocks as $container_stock){
            $data = [
                "token" => ANT::getToken(),
                "container_number" => $container_stock->container_number,
                "container_type" => $container_stock->container_type,
                "status1" => $container_stock->status, /* incoming, received, in_order, shipped */
                "state" => $container_stock->state ?? "",
                "truck_number" => $container_stock->car_number_carriage ?? "",
                "seal_number_doc" => $container_stock->seal_number_document ?? "",
                "seal_number_fact" => $container_stock->seal_number_fact ?? "",
                "notes" => $container_stock->note ?? "",
                "start_datetime" => $container_stock->created_at, // время получение пропуска у охраны
                "end_datetime" => $container_stock->updated_at, // время выхода из территории
                "counterparty" => $container_stock->contractor ?? "",
                "company" => $container_stock->company ?? "",
                "current" => "string",
                "zone" => $container_stock->zone,
                "title" => $container_stock->title,
                'name' => $container_stock->name,
                'row' => $container_stock->row,
                'place' => $container_stock->place,
                'floor' => $container_stock->floor,
            ];

            if(ANT::stockUpdate($data)) {
                $this->info($container_stock->container_number . " successfully sent.");
            } else {
                $this->error($container_stock->container_number . " could not be sent.");
            }
        }
    }
}
