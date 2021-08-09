<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableContainerLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('container_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('container_id');
            $table->string('container_number');
            $table->enum('operation_type', ['incoming', 'received', 'in_order', 'shipped', 'completed']);
            $table->unsignedBigInteger('technique_id')->nullable();
            $table->string('address_from')->nullable();
            $table->string('address_to')->nullable();
            $table->string('state')->nullable();
            $table->timestamp('transaction_date');
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');

            $table->foreign('container_id')
                ->references('id')->on('containers')
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
        Schema::dropIfExists('container_logs');
    }
}
