<?php

namespace App\Console\Commands;

use App\Models\Container;
use App\Models\ContainerAddress;
use App\Models\ContainerLog;
use App\Models\ContainerStock;
use Illuminate\Console\Command;
use Str;

class ImportContainer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:container';

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
        $path_to_file = 'files/9224693.xlsx';
        $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify(public_path($path_to_file));
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
        $spreadsheet = $reader->load(public_path($path_to_file));
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        $count = 0;
        $cnt = 0;
        foreach ($sheetData as $key=>$arr) {
            if ($key == 1) continue;
            $zone = $arr['A'];
            if ($zone == 'Спредер') {
                $kind = 'r';
            }elseif ($zone == 'Спредер-консоль') {
                $kind = 'k';
            } else {
                $kind = 'r';
            }

            if ($zone == 'Виртуальная') {
                $row = 1;
                $place = 1;
                $floor = 1;
            } else {
                $row = $arr['B'];
                $place = $arr['C'];
                $floor = $arr['D'];
            }

            $name = strtoupper(substr(Str::slug($zone), 0,2).$kind."-".$row."-".$place."-".$floor);
            $number = strtoupper($arr['E']);
            $container_type = ($arr['F'] == 45) ? '40' : $arr['F'];
            $container = Container::whereNumber($number)->first();
            if (!$container){
                $container = Container::create([
                    'number' => $number, 'container_type' => (string) $container_type
                ]);
            }

            if ($zone == 'Виртуальная') {
                $container_address = ContainerAddress::findOrFail(1);
            } else {
                $container_address = ContainerAddress::whereName($name)->first();
            }

            if ($container_address) {
                $container_stock = ContainerStock::where(['container_id' => $container->id, 'container_address_id' => $container_address->id])->first();
                if ($container_stock) {
                    $this->info("The container: $number is not added. It is already in the stock with the same address $name.");
                    $cnt++;
                } else {
                    ContainerStock::create([
                        'container_id' => $container->id, 'container_address_id' => $container_address->id, 'status' => 'received'
                    ]);
                    ContainerLog::create([
                        'user_id' => 140, 'container_id' => $container->id, 'container_number' => $number, 'operation_type' => 'incoming',
                        'address_from' => 'из файла', 'address_to' => $container_address->name
                    ]);
                    $this->info("The container: $number successfully added.");
                    $count++;
                }
            } else {
                $this->info("The container: $number is not added. The address $name is wrong.");
                $cnt++;
            }
        }
        $this->info("*****************************************************************");
        $this->info("Not added to stock $cnt containers.");
        $this->info("Added to stock $count containers. The process import completed.");
    }
}
