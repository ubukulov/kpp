<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MarkingAggregations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('marking_aggregations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sscc_pallet_id')->nullable();
            $table->unsignedBigInteger('sscc_box_id')->nullable();
            $table->string('gtin')->nullable();
            $table->string('article')->nullable();
            $table->string('km', 500)->nullable();
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
        Schema::dropIfExists('marking_aggregations');
    }
}
