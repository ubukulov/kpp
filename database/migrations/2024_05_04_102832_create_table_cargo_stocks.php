<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableCargoStocks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cargo_stocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cargo_id');
            $table->unsignedBigInteger('cargo_item_id');
            $table->enum('status', [
                'incoming', 'received', 'in_order', 'shipped'
            ]);
            $table->timestamps();

            $table->foreign('cargo_id')
                ->references('id')->on('cargo')
                ->onDelete('cascade');

            $table->foreign('cargo_item_id')
                ->references('id')->on('cargo_items')
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
        Schema::dropIfExists('cargo_stocks');
    }
}
