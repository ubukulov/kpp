<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTablePermitStatuses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permit_statuses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('permit_id');
            $table->string('status')->nullable();
            $table->string('description')->nullable();

            $table->timestamps();

            $table->foreign('permit_id')
                ->references('id')
                ->on('permits')
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
        Schema::dropIfExists('permit_statuses');
    }
}
