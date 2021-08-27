<?php

namespace App\Console\Commands;

use App\Models\ContainerAddress;
use Illuminate\Console\Command;
use Str;

class CreateContainerAddress extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'container-address:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Зона спредер: с 1 по 12 ряду 10место. с 13 по 25 ряду 8 место';

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
        $title = "СПРЕДЕР";
        $kind = $this->ask('Enter kind: ');
        $zone = strtoupper(substr(Str::slug($title),0,2).$kind);

        $iteration = $this->ask('Enter iteration: ');
        $row = $this->ask('Enter rows: ');
        $place = $this->ask('Enter places: ');
        $floor = $this->ask('Enter floors: ');

        $container_address = ContainerAddress::whereName('damu_in')->first();
        if (!$container_address) {
            ContainerAddress::create([
                'title' => 'DAMU_IN', 'zone' => 'DAMU_IN', 'kind' => 'in', 'row' => 1, 'place' => 1, 'floor' => 1, 'name' => 'damu_in'
            ]);
        }

        $container_address = ContainerAddress::whereName('damu_out')->first();
        if (!$container_address) {
            ContainerAddress::create([
                'title' => 'DAMU_OUT', 'zone' => 'DAMU_OUT', 'kind' => 'out', 'row' => 1, 'place' => 1, 'floor' => 1, 'name' => 'damu_out'
            ]);
        }

        $container_address = ContainerAddress::whereName('pole')->first();
        if (!$container_address) {
            ContainerAddress::create([
                'title' => 'ПОЛЕ', 'zone' => 'POLE', 'kind' => 'pole', 'row' => 1, 'place' => 1, 'floor' => 1, 'name' => 'pole'
            ]);
        }

        $container_address = ContainerAddress::whereName('spreder')->first();
        if(!$container_address) {
            ContainerAddress::create([
                'title' => 'СПРЕДЕР', 'zone' => 'SPREADER', 'kind' => 'spreder', 'row' => 1, 'place' => 1, 'floor' => 1, 'name' => 'spreder'
            ]);
        }

        for ($i=$iteration; $i<=$row; $i++) {
            for($j=1; $j<=$place; $j++) {
                for($k=1; $k<=$floor; $k++) {
                    $data = [
                        'title' => $title,
                        'zone' => $zone,
                        'kind' => $kind,
                        'row'  => $i,
                        'place' => $j,
                        'floor' => $k,
                        'name' => $zone."-".$i."-".$j."-".$k
                    ];
                    ContainerAddress::create($data);
                }
            }
        }
    }
}
