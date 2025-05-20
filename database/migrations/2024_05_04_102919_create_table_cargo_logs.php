<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableCargoLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cargo_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('cargo_id')->nullable();
            $table->unsignedBigInteger('cargo_task_id')->nullable();
            $table->string('user_name')->nullable();
            $table->string('cargo_name')->nullable();
            $table->string('vin_code')->nullable();
            $table->string('car_number')->nullable();
            $table->string('quantity')->nullable();
            $table->string('weight')->nullable();
            $table->string('address_from')->nullable();
            $table->string('address_to')->nullable();

            $table->enum('action_type', [
                'incoming', 'received', 'moved', 'in_order', 'shipped', 'completed', 'canceled', 'ignore'
            ]);

            /*
             * incoming - когда заявка на прием
             * received - когда приняли и размещен
             * in_order - на выдачу
             * shipped -  выдан
             * moved - перемещен
             * completed - завершен
             * canceled - отменен
             * ignore -  проигнорирован
             */

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cargo_logs');
    }
}
