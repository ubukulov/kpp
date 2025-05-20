<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableCargoTaskServices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cargo_task_services', function (Blueprint $table) {
            $table->unsignedBigInteger('cargo_task_id');
            $table->unsignedBigInteger('cargo_service_id');

            $table->foreign('cargo_task_id')->references('id')->on('cargo_tasks')->onDelete('cascade');
            $table->foreign('cargo_service_id')->references('id')->on('cargo_services')->onDelete('cascade');
            $table->primary(['cargo_task_id','cargo_service_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cargo_task_services');
    }
}
