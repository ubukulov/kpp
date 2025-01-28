<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableTechniqueLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('technique_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->integer('technique_task_id');
            $table->string('owner')->nullable();
            $table->string('technique_type')->nullable();
            $table->string('mark')->nullable();
            $table->string('vin_code')->nullable();
            $table->enum('operation_type', ['incoming', 'received', 'in_order', 'shipped', 'completed', 'canceled', 'moved']);
            $table->string('address_from')->nullable();
            $table->string('address_to')->nullable();
            $table->enum('defect', ['no', 'yes']);
            $table->string('defect_note')->nullable();
            $table->string('defect_image')->nullable();

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');

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
        Schema::dropIfExists('technique_logs');
    }
}
