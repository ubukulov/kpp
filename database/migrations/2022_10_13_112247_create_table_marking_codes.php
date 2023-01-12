<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableMarkingCodes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('marking_codes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('marking_details_id');
            $table->unsignedBigInteger('scan_user_id')->nullable();
            $table->enum('status', [
                'not_marked', 'marked'
            ]);
            $table->string('box_number')->nullable();
            $table->string('marking_code', 500)->nullable();
            $table->timestamps();

            $table->foreign('marking_details_id')
                ->references('id')->on('marking_details')
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
        Schema::dropIfExists('marking_codes');
    }
}
