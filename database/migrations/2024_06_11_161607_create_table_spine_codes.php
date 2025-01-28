<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableSpineCodes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('spine_codes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('spine_id');
            $table->unsignedBigInteger('technique_task_id');
            $table->string('vin_code');
            $table->timestamps();

            $table->foreign('spine_id')
                ->references('id')->on('spines')
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
        Schema::dropIfExists('spine_codes');
    }
}
