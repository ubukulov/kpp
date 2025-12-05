<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableWagonStocks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wagon_stocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('wagon_id');
            $table->unsignedBigInteger('wagon_track_id');
            $table->unsignedBigInteger('wagon_track_destination_id');
            $table->unsignedBigInteger('wagon_task_id')->nullable();
            $table->timestamp('reception_datetime')->nullable();
            $table->string('ordinal_number')->nullable();
            $table->string('country_code')->nullable();
            $table->string('owner_name')->nullable(); // владелец вагона
            $table->string('state')->nullable(); // состояние вагона
            $table->string('destination_station')->nullable(); // Станция назначения
            $table->string('direction')->nullable(); // Направления
            $table->string('shipper_name')->nullable(); // Наименование грузоотправителя
            $table->string('consignee_name')->nullable(); // наименование грузополучателя
            $table->string('cargo_name')->nullable(); // наименование груза
            $table->enum('status', [
                'submission',
                'movement',
                'cleaning_request',
                'moving_request'
            ]);
            $table->enum('operation_type', [
                'unloading', // выгрузка
                'loading',  // погрузка
                'customs', // расстаможка
                'nothing' // ничего
            ]);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('wagon_id')->references('id')->on('wagons')->onDelete('cascade');
            $table->foreign('wagon_track_id')->references('id')->on('wagon_tracks')->onDelete('cascade');
            $table->foreign('wagon_track_destination_id')->references('id')->on('wagon_tracks')->onDelete('cascade');
            $table->foreign('wagon_task_id')->references('id')->on('wagon_tasks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wagon_stocks');
    }
}
