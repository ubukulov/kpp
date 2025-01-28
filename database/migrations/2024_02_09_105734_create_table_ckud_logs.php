<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableCkudLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ckud_logs', function (Blueprint $table) {
            $table->id();
            $table->string('LogMessageSubType')->nullable();
            $table->string('LogMessageType')->nullable();
            $table->string('Message')->nullable();
            $table->bigInteger('ckud_id');
            $table->dateTime('DateTime')->nullable();
            $table->string('Details')->nullable();
            $table->string('DriverID')->nullable();
            $table->string('DriverName')->nullable();
            $table->string('EmployeeFirstName')->nullable();
            $table->string('EmployeeLastName')->nullable();
            $table->string('EmployeeSecondName')->nullable();
            $table->string('EmployeeTableNumber')->nullable();
            $table->string('EmployeeID')->nullable();
            $table->string('EmployeeGroupFullName')->nullable();
            $table->string('EmployeeGroupId')->nullable();
            $table->string('EmployeeGroupName')->nullable();

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
        Schema::dropIfExists('ckud_logs');
    }
}
