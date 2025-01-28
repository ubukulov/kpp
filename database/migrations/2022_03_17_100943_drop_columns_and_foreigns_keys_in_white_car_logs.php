<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropColumnsAndForeignsKeysInWhiteCarLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('white_car_logs', function (Blueprint $table) {
            $table->dropForeign('white_car_logs_wcl_id_foreign');
            $table->dropForeign('white_car_logs_company_id_foreign');
            $table->dropColumn(['wcl_id', 'company_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('white_car_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('wcl_id')->after('id');
            $table->unsignedBigInteger('company_id')->after('wcl_id');

            $table->foreign('wcl_id')
                ->references('id')->on('white_car_lists')
                ->onDelete('cascade');

            $table->foreign('company_id')
                ->references('id')->on('companies')
                ->onDelete('cascade');
        });
    }
}
