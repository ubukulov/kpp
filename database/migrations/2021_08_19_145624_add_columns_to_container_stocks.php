<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToContainerStocks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('container_stocks', function (Blueprint $table) {
            $table->unsignedBigInteger('container_task_id')->after('id')->nullable();
            $table->string('company')->after('state')->nullable();
            $table->enum('customs', ['yes', 'not'])->after('state');
            $table->string('car_number_carriage')->after('customs');
            $table->string('seal_number_document')->after('car_number_carriage')->nullable();
            $table->string('seal_number_fact')->after('seal_number_document')->nullable();
            $table->string('note')->after('seal_number_fact')->nullable();
            $table->string('datetime_submission')->after('note');
            $table->string('datetime_arrival')->after('datetime_submission');
            $table->string('contractor')->after('datetime_arrival')->nullable();

            $table->foreign('container_task_id')
                ->references('id')->on('container_tasks')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('container_stocks', function (Blueprint $table) {
            $table->dropColumn([
                'container_task_id',
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
