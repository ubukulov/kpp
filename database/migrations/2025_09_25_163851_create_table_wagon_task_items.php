<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableWagonTaskItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wagon_task_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('wagon_task_id')->nullable();
            $table->unsignedBigInteger('wagon_track_id')->nullable();
            $table->unsignedBigInteger('wagon_track_destination_id')->nullable();
            $table->unsignedBigInteger('wagon_id')->nullable();
            $table->string('user_ids')->nullable();
            $table->string('wagon_number')->nullable();
            $table->string('cargo_name')->nullable();
            $table->string('owner_name')->nullable();
            $table->timestamp('reception_datetime')->nullable();
            $table->string('country_code')->nullable();
            $table->string('state')->nullable();
            $table->string('ordinal_number')->nullable();
            $table->string('destination_station')->nullable(); // Станция назначения
            $table->string('direction')->nullable(); // Направления
            $table->string('shipper_name')->nullable(); // Наименование грузоотправителя
            $table->string('consignee_name')->nullable(); // наименование грузополучателя

            $table->enum('status', [
                'submission',
                'movement',
                'cleaning_request',
                'cleaning_confirmed'
            ]);

            $table->enum('operation_type', [
                'unloading', // выгрузка
                'loading',  // погрузка
                'customs', // расстаможка
                'nothing' // ничего
            ]);

            $table->timestamps();

            $table->foreign('wagon_task_id')->references('id')->on('wagon_tasks')->onDelete('cascade');
            $table->foreign('wagon_id')->references('id')->on('wagons')->onDelete('set null');
            $table->foreign('wagon_track_id')->references('id')->on('wagon_tracks')->onDelete('set null');
            $table->foreign('wagon_track_destination_id')->references('id')->on('wagon_tracks')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wagon_task_items');
    }
}
