<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableWagonLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wagon_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('wagon_id')->nullable();
            $table->unsignedBigInteger('container_id')->nullable();
            $table->unsignedBigInteger('wagon_track_id')->nullable();
            $table->unsignedBigInteger('wagon_task_id')->nullable();
            $table->string('user_ids')->nullable();
            $table->string('address_from')->nullable();
            $table->string('address_to')->nullable();
            $table->timestamp('reception_datetime')->nullable();
            $table->string('wagon_number')->nullable();
            $table->string('wagon_task_number')->nullable();
            $table->string('container_number')->nullable();
            $table->string('ordinal_number')->nullable();
            $table->string('country_code')->nullable();
            $table->string('owner_name')->nullable();
            $table->string('cargo_name')->nullable();
            $table->string('state')->nullable();
            $table->string('destination_station')->nullable(); // Станция назначения
            $table->string('directions')->nullable(); // Направления
            $table->string('shipper_name')->nullable(); // Наименование грузоотправителя

            $table->enum('status', [
                'submission', // подача
                'movement', // перемещение
                'cleaning_request', // запрос на уборку
                'cleaning_confirmed', // уборка подтвержден
                'detach_container', // откреплен контейнер
                'attach_container', // прикреплен контейнер
                'moving_request', // запрос на перемещение
            ]);
            $table->enum('operation_type', [
                'unloading', // выгрузка
                'loading',  // погрузка
                'customs', // расстаможка
                'nothing' // ничего
            ]);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('wagon_id')->references('id')->on('wagons')->onDelete('set null');
            $table->foreign('container_id')->references('id')->on('containers')->onDelete('set null');
            $table->foreign('wagon_track_id')->references('id')->on('wagon_tracks')->onDelete('set null');
            $table->foreign('wagon_task_id')->references('id')->on('wagon_tasks')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wagon_logs');
    }
}
