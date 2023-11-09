<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableAlertItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alert_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('alert_id');
            $table->tinyInteger('sms')->default(0);
            $table->tinyInteger('voice')->default(0);
            $table->tinyInteger('whatsapp')->default(0);
            $table->tinyInteger('interval');
            $table->string('message_sms')->nullable();
            $table->string('message_voice')->nullable();
            $table->string('message_whatsapp')->nullable();
            $table->timestamps();

            $table->foreign('alert_id')
                ->references('id')->on('alerts')
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
        Schema::dropIfExists('alert_items');
    }
}
