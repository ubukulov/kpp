<?php

namespace App\Console\Commands;

use App\Car;
use App\Permit;
use Illuminate\Console\Command;

class WhatsApp extends Command
{
    protected $apiTokenInstance = 'df56c440260a8459cc577fd02698f0a0e9e5f23676b5582b70';
    protected $idInstance = 7185;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:run {msg}';

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
        $api_send_message = "https://api.green-api.com/waInstance".$this->idInstance."/sendMessage/".$this->apiTokenInstance;
        $message = $this->argument('msg');

        /*Permit::chunk(100, function($drivers) use ($api_send_message, $message){
            foreach ($drivers as $driver) {
                $number = str_replace(" ", "", $driver->phone);
                if(strlen($number) == 11) {
                    $number = "7".substr($number, 1)."@c.us";
                    $data = [
                        'chatId' => $number,
                        'message' => $message
                    ];
                    $client = new \GuzzleHttp\Client();
                    $response = $client->request('POST', $api_send_message, [
                        \GuzzleHttp\RequestOptions::JSON => $data,
                        'headers' => [
                            'Content-Type' => 'application/json',
                        ]
                    ]);
                    //$this->info("The driver with number ".$number." sent message. Result code: ".$response->getStatusCode());
                }
            }
        });*/

        Car::where('cars.lc_id', '>', 1)
            ->select('permits.phone')
            ->join('permits', 'permits.tex_number', '=', 'cars.tex_number')
            ->orderBy('cars.id')
            ->groupBy('cars.tex_number')
            ->chunk(100, function($drivers) use ($api_send_message, $message){
                foreach ($drivers as $driver) {
                    $number = str_replace(" ", "", $driver->phone);
                    if(strlen($number) == 11) {
                        $number = "7".substr($number, 1).'@c.us';
                        $data = [
                            'chatId' => $number,
                            'message' => $message
                        ];
                        $data = json_encode($data);
                        $curl = curl_init();
                        curl_setopt_array(
                            $curl,
                            [
                                CURLOPT_URL => $api_send_message,
                                CURLOPT_RETURNTRANSFER => true,
                                CURLOPT_ENCODING => "",
                                CURLOPT_MAXREDIRS => 10,
                                CURLOPT_TIMEOUT => 0,
                                CURLOPT_FOLLOWLOCATION => true,
//                CURLOPT_HTTP_VERSION => CURLOPT_HTTP_VERSION_1_1,
                                CURLOPT_CUSTOMREQUEST => 'POST',
                                CURLOPT_POSTFIELDS => $data,
                                CURLOPT_HTTPHEADER => [
                                    "Content-Type: application/json"
                                ]
                            ]
                        );
                        $res = curl_exec($curl);
                        curl_close($curl);
                        dd($res);
                    }
                }
            });
    }
}
