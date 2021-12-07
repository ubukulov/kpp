<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToWhiteCarLists extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('white_car_lists', function (Blueprint $table) {
            $table->string('full_name')->after('status')->nullable();
            $table->string('position')->after('full_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('white_car_lists', function (Blueprint $table) {
            $table->dropColumns(['full_name', 'position']);
        });
    }
}
