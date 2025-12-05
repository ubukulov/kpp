<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableServiceContractorServices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_contractor_services', function (Blueprint $table) {

            $table->unsignedBigInteger('service_id');
            $table->unsignedBigInteger('service_contractor_id');

            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
            $table->foreign('service_contractor_id')->references('id')->on('service_contractors')->onDelete('cascade');

            $table->primary(['service_id', 'service_contractor_id'], 'svc_contr_services_pk');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service_contractor_services');
    }
}
