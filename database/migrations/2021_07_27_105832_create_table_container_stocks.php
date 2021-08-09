<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableContainerStocks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('container_stocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('container_id');
            $table->unsignedBigInteger('container_address_id');
            $table->enum('status', ['incoming', 'received', 'in_order', 'shipped']);
            $table->string('state')->nullable();
            $table->timestamps();

            $table->foreign('container_id')
                ->references('id')->on('containers')
                ->onDelete('cascade');

            $table->foreign('container_address_id')
                ->references('id')->on('container_address')
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
        Schema::dropIfExists('container_stocks');
    }
}
