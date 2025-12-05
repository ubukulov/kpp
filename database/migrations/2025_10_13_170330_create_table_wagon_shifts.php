<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableWagonShifts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wagon_shifts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('wagon_log_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('wagon_log_id')->references('id')->on('wagon_logs')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wagon_shifts');
    }
}
