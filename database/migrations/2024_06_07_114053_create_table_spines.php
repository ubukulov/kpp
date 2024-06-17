<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableSpines extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('spines', function (Blueprint $table) {
            $table->id();
            $table->string('spine_number')->unique();
            $table->string('company')->nullable();
            $table->string('technique_task_number')->nullable();
            $table->enum('type', ['receive', 'ship']);
            $table->string('name')->nullable();
            $table->string('container_number')->nullable();
            $table->string('car_number')->nullable();
            $table->string('driver_name')->nullable();
            $table->string('user_name')->nullable();

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
        Schema::dropIfExists('spines');
    }
}
