<?php

use Illuminate\Database\Seeder;
use App\Models\Kpp;

class KppSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            0 => [
                'title' => 'КПП1',
                'name' => 'kpp1'
            ],
            1 => [
                'title' => 'КПП2',
                'name' => 'kpp2'
            ],
            3 => [
                'title' => 'КПП3',
                'name' => 'kpp3'
            ],
            4 => [
                'title' => 'КПП4',
                'name' => 'kpp4'
            ],
            5 => [
                'title' => 'КПП5',
                'name' => 'kpp5'
            ],
        ];
        foreach ($data as $key=>$arr) {
            Kpp::create($arr);
        }
    }
}
