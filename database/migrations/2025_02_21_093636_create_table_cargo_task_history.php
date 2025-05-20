<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableCargoTaskHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cargo_task_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cargo_id');
            $table->unsignedBigInteger('cargo_task_id');
            $table->unsignedBigInteger('user_id');
            $table->string('vin_code')->nullable();
            $table->enum('status', [
                'incoming', 'received', 'in_order', 'shipped', 'completed', 'canceled'
            ]);
            $table->string('image')->nullable();
            $table->timestamps();

            $table->foreign('cargo_task_id')
                ->references('id')->on('cargo_tasks')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');

            $table->foreign('cargo_id')
                ->references('id')->on('cargo')
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
        Schema::dropIfExists('cargo_task_history');
    }
}
