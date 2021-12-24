<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChangeEnumColumnInTableContainerStocksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("ALTER TABLE container_stocks CHANGE COLUMN status status ENUM('incoming', 'received', 'in_order', 'shipped', 'cancel','edit')");
    }
}
