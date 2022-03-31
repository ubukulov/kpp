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
    protected $description = 'Таможенный зона занимает 1 ряд. И Максимально может вместиться до 10 контейнеров.
                              Зона спредер: с 2 по 12 ряду 10место. с 13 по 25 ряду 8 место';

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

        $container_address = ContainerAddress::whereName('buffer')->first();
        if (!$container_address) {
            ContainerAddress::create([
                'title' => 'Буфер', 'zone' => 'BUFFER', 'kind' => 'buffer', 'row' => 1, 'place' => 1, 'floor' => 1, 'name' => 'buffer'
            ]);
        }

        $container_address = ContainerAddress::whereName('spreder')->first();
        if(!$container_address) {
            ContainerAddress::create([
                'title' => 'СПРЕДЕР', 'zone' => 'SPREDER', 'kind' => 'spreder', 'row' => 1, 'place' => 1, 'floor' => 1, 'name' => 'spreder'
            ]);
        }

        if ($kind == 'k') {
            for ($i=1; $i<=$row; $i++) {
                $s1 = $zone."-".$i."-1-1";
                $s2 = $zone."-".$i."-1-2";
                $s3 = $zone."-".$i."-2-1";
                $s4 = $zone."-".$i."-2-2";
                $s5 = $zone."-".$i."-2-3";
                $data = [
                    'title' => $title,
                    'zone' => $zone,
                    'kind' => 'k',
                    'row'  => $i,
                ];
                $data['place'] = 1;
                $data['floor'] = 1;
                $data['name'] = $s1;
                ContainerAddress::create($data);

                $data['place'] = 1;
                $data['floor'] = 2;
                $data['name'] = $s2;
                ContainerAddress::create($data);

                $data['place'] = 2;
                $data['floor'] = 1;
                $data['name'] = $s3;
                ContainerAddress::create($data);

                $data['place'] = 2;
                $data['floor'] = 2;
                $data['name'] = $s4;
                ContainerAddress::create($data);

                $data['place'] = 2;
                $data['floor'] = 3;
                $data['name'] = $s5;
                ContainerAddress::create($data);
            }
        } else {
            for ($i=$iteration; $i<=$row; $i++) {
                for($j=1; $j<=$place; $j++) {
                    for($k=1; $k<=$floor; $k++) {
                        $name = $zone."-".$i."-".$j."-".$k;
                        $c_address = ContainerAddress::whereName($name)->first();
                        if (!$c_address) {
                            $data = [
                                'title' => $title,
                                'zone' => $zone,
                                'kind' => $kind,
                                'row'  => $i,
                                'place' => $j,
                                'floor' => $k,
                                'name' => $name
                            ];
                            ContainerAddress::create($data);
                        }
                    }
                }
            }
        }
    }
}
