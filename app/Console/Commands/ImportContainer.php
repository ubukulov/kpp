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
        $path_to_file = 'files/INV021122/20.xlsx';
        $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify(public_path($path_to_file));
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
        $spreadsheet = $reader->load(public_path($path_to_file));
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        $count = 0;
        $cnt = 0;
        foreach ($sheetData as $key=>$arr) {
            if ($key == 1) continue;
            $zone = trim($arr['A']);
            $row = $arr['B'];
            $place = $arr['C'];
            $floor = $arr['D'];
            $number = trim(strtoupper($arr['E']));
            if($number == '') {
                continue;
            }

            if(empty($arr['F']) || $arr['F'] == 40) {
                $container_type = '40';
            } elseif($arr['F'] == 45) {
                $container_type = '45';
            } elseif($arr['F'] == 20) {
                $container_type = '20';
            }

            switch ($zone) {
                case "30-ка";
                    $slag = '30R';
                    $name = $slag."-".$row."-".$place."-".$floor;
                break;

                case "5 скл";
                    $slag = '5-R';
                    $name = $slag."-".$row."-".$place."-".$floor;
                    break;

                case "Ангар";
                    $slag = 'ANR';
                    $name = $slag."-".$row."-".$place."-".$floor;
                    break;

                case "Поле";
                    $slag = 'POPOLE';
                    $name = $slag."-".$row."-".$place."-".$floor;
                    break;

                case "Ричстакер";
                    $slag = 'RICH';
                    $name = $slag."-".$row."-".$place."-".$floor;
                    break;

                case "Спр.конс.";
                    $slag = 'SPK';
                    $name = $slag."-".$row."-".$place."-".$floor;
                    break;

                case "Спредер";
                    $slag = 'SPR';
                    $name = $slag."-".$row."-".$place."-".$floor;
                    break;

                case "ТС";
                    $slag = 'TSR';
                    $name = $slag."-".$row."-".$place."-".$floor;
                    break;

                case "ТАМОЖЕННЫЙ ДОСМОТР";
                    $slag = 'ZTCIA';
                    $name = $slag."-".$row."-".$place."-".$floor;
                    break;

                default:
                    $name = 'damu_in';
                    break;
            }

            //$state = (empty($arr['G'])) ? null : $arr['G'];
            $container = Container::whereNumber($number)->first();
            if (!$container){
                $container = Container::create([
                    'number' => $number, 'container_type' => (string) $container_type
                ]);
            }

            $container_address = ContainerAddress::whereName($name)->first();

            if ($container_address) {
                $container_stock = ContainerStock::where(['container_id' => $container->id])->first();
                if ($container_stock) {
//                    if($container_stock->container_address_id != $container_address->id) {
//                        $container_stock->container_address_id = $container_address->id;
//                        $container_stock->status = 'received';
//                        $container_stock->note = 'Invent2022';
//                        $container_stock->save();
//                        $count++;
//                    }
                    continue;
                } else {
                    ContainerStock::create([
                        'container_id' => $container->id, 'container_address_id' => $container_address->id, 'status' => 'received',
                        'note' => 'Invent '.date('d.m.Y')
                    ]);
                    ContainerLog::create([
                        'user_id' => 116, 'container_id' => $container->id, 'container_number' => $number, 'operation_type' => 'incoming',
                        'address_from' => 'из файла', 'address_to' => $container_address->name,
                        'note' => 'По инвенту '.date('d.m.Y')
                    ]);
                    //$this->info("The container: $number successfully added.");
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
