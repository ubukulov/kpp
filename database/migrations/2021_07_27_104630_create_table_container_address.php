<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableContainerAddress extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('container_address', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('zone');
            $table->enum('kind', ['r', 'k', 'in', 'out', 'pole', 'spreder']);
            $table->integer('row');
            $table->integer('place');
            $table->integer('floor');
            $table->string('name')->unique();
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
        Schema::dropIfExists('container_address');
    }
}
