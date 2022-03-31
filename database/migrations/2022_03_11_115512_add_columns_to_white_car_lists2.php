<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToWhiteCarLists2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('white_car_lists', function (Blueprint $table) {
            $table->integer('pass_type')->after('kpp_name')->default(1);
            $table->string('contractor_name')->after('pass_type')->nullable();
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
            $table->dropColumn(['pass_type', 'contractor_name']);
        });
    }
}
