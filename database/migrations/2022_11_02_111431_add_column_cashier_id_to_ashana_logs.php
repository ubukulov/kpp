<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnCashierIdToAshanaLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ashana_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('cashier_id')->after('id')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ashana_logs', function (Blueprint $table) {
            $table->dropColumn('cashier_id');
        });
    }
}
