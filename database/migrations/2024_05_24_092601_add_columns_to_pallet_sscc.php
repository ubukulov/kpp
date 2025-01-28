<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToPalletSscc extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pallet_sscc', function (Blueprint $table) {
            $table->unsignedBigInteger('task_id')->after('user_id');
            $table->enum('status', [
                'waiting', 'confirmed', 'failed'
            ])->after('code');
            $table->enum('type', [
                'box', 'pallet'
            ])->after('status');
            $table->integer('standard')->after('type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pallet_sscc', function (Blueprint $table) {
            $table->dropColumn(['task_id', 'status', 'standard']);
        });
    }
}
