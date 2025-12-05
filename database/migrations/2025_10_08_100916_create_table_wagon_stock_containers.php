<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableWagonStockContainers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wagon_stock_containers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('wagon_stock_id');
            $table->unsignedBigInteger('container_id');
            $table->string('container_number')->nullable();
            $table->string('weight')->nullable(); // вес контейнера
            $table->string('consignee_name')->nullable(); // наименование грузополучателя
            $table->string('seal_number_document')->nullable(); // номер пломбы по документам
            $table->string('seal_number_fact')->nullable(); // номер пломбы по факту
            $table->string('cargo_name')->nullable(); // наименование груза
            $table->string('shipper_name')->nullable(); // наименование грузоотправителя
            $table->timestamps();

            $table->foreign('wagon_stock_id')->references('id')->on('wagon_stocks')->onDelete('cascade');
            $table->foreign('container_id')->references('id')->on('containers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wagon_stock_containers');
    }
}
