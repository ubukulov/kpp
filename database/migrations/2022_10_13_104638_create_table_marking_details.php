<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableMarkingDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('marking_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('marking_id');
            $table->string('container_number')->nullable();
            $table->string('invoice_number')->nullable();
            $table->string('gtin')->unique();
            $table->string('line1')->nullable();
            $table->string('line2')->nullable();
            $table->string('line3')->nullable();
            $table->string('line4')->nullable();
            $table->string('line5')->nullable();
            $table->string('line6')->nullable();
            $table->string('line7')->nullable();
            $table->string('line8')->nullable();
            $table->string('line9')->nullable();
            $table->string('line10')->nullable();
            $table->string('line11')->nullable();
            $table->enum('eac', [
                'Y', 'N'
            ]);
            $table->enum('mc', [
                'Y', 'N'
            ]);
            $table->timestamps();

            $table->foreign('marking_id')
                ->references('id')->on('marking')
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
        Schema::dropIfExists('marking_details');
    }
}
