<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToContainerLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('container_logs', function (Blueprint $table) {
            $table->timestamp('start_date')->after('state')->nullable();
            $table->enum('action_type', [
                'reception', 'ship', 'put', 'pick', 'move', 'move_another_zone', 'canceled', 'deleted', 'edit'
            ])->after('transaction_date');
            /*
             * reception = заявка на прием
             * ship = заявка на выдачу
             * put = размещен
             * pick = выдан
             * move = перемещение внутри зоны
             * move_another_zone = перемещение в другую зону
             * canceled = отменен из стока
             * deleted = удален из стока
             * edit = запрос на изменение номера контейнера
             * */
            $table->string('company')->after('action_type')->nullable();
            $table->enum('customs', ['yes', 'not'])->after('company');
            $table->string('car_number_carriage')->after('customs');
            $table->string('seal_number_document')->after('car_number_carriage')->nullable();
            $table->string('seal_number_fact')->after('seal_number_document')->nullable();
            $table->string('note')->after('seal_number_fact')->nullable();
            $table->string('datetime_submission')->after('note');
            $table->string('datetime_arrival')->after('datetime_submission');
            $table->string('contractor')->after('datetime_arrival')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('container_logs', function (Blueprint $table) {
            $table->dropColumn([
                'action_type',
                'company',
                'customs',
                'car_number_carriage',
                'seal_number_document',
                'seal_number_fact',
                'note',
                'datetime_submission',
                'datetime_arrival',
                'contractor'
            ]);
        });
    }
}
