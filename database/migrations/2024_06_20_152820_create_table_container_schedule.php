<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableContainerSchedule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('container_schedule', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // ИД начальника смены кто формирует график работы
            $table->integer('crane_id'); // ИД крановщика
            $table->string('ids')->nullable(); // ID стропальщиков
            $table->unsignedBigInteger('technique_id'); // ИД техники
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');

            $table->foreign('technique_id')
                ->references('id')->on('techniques')
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
        Schema::dropIfExists('containerSchedule');
    }
}
