<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableCargoStocks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cargo_stocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cargo_id');
            $table->unsignedBigInteger('cargo_task_id');
            $table->unsignedBigInteger('cargo_area_id')->nullable();
            $table->string('vin_code')->nullable();
            $table->integer('quantity')->nullable();
            $table->string('weight')->nullable();
            $table->string('car_number')->nullable();
            $table->enum('status', [
                'incoming', 'received', 'in_order', 'shipped'
            ]);
            $table->string('image')->nullable();

            $table->timestamps();

            $table->foreign('cargo_id')
                ->references('id')->on('cargo')
                ->onDelete('cascade');

            $table->foreign('cargo_task_id')
                ->references('id')->on('cargo_tasks')
                ->onDelete('cascade');

            $table->foreign('cargo_area_id')
                ->references('id')->on('cargo_areas')
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
        Schema::dropIfExists('cargo_stocks');
    }
}
