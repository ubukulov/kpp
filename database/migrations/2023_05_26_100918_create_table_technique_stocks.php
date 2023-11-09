<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableTechniqueStocks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('technique_stocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('technique_task_id');
            $table->unsignedBigInteger('technique_type_id');
            $table->unsignedBigInteger('technique_place_id')->nullable();
            $table->string('owner')->nullable();
            $table->string('mark')->nullable();
            $table->string('vin_code')->unique();
            $table->enum('status', ['incoming', 'received', 'in_order', 'shipped']);
            $table->string('image')->nullable();
            $table->timestamps();

            $table->foreign('technique_task_id')
                ->references('id')->on('technique_tasks')
                ->onDelete('cascade');

            $table->foreign('technique_type_id')
                ->references('id')->on('technique_types')
                ->onDelete('cascade');

            $table->foreign('technique_place_id')
                ->references('id')->on('technique_places')
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
        Schema::dropIfExists('technique_stocks');
    }
}
