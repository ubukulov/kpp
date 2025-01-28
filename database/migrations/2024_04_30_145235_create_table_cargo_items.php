<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableCargoItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cargo_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cargo_id')->nullable();
            $table->unsignedBigInteger('cargo_area_id')->nullable();
            $table->unsignedBigInteger('cargo_tonnage_type_id')->nullable();
            $table->string('cargo_tonnage_mark')->nullable();
            $table->string('vincode')->nullable();
            $table->string('technique_ids')->nullable();
            $table->string('car_number')->nullable();
            $table->integer('count_place')->nullable();
            $table->double('weight')->nullable();
            $table->double('square')->nullable();
            $table->string('employee_ids')->nullable();
            $table->string('cargo_work_type_ids')->nullable();
            $table->enum('type', ['manual', 'automatic'])->nullable();
            $table->enum('status', [
                'waiting', 'processing', 'completed', 'canceled'
            ]);

            $table->timestamps();

            $table->foreign('cargo_id')
                ->references('id')->on('cargo')
                ->onDelete('cascade');

            $table->foreign('cargo_area_id')
                ->references('id')->on('cargo_areas')
                ->onDelete('cascade');

            $table->foreign('cargo_tonnage_type_id')
                ->references('id')->on('cargo_tonnage_types')
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
        Schema::dropIfExists('cargo_items');
    }
}
