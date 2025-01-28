<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsContainersNumberToPermitTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('permits', function (Blueprint $table) {
            $table->string('incoming_container_number')->after('status')->nullable();
            $table->string('outgoing_container_number')->after('status')->nullable();
            $table->string('note')->after('outgoing_container_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('permits', function (Blueprint $table) {
            $table->dropColumn(['incoming_container_number', 'outgoing_container_number', 'note']);
        });
    }
}
