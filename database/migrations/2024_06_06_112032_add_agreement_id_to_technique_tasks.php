<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAgreementIdToTechniqueTasks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('technique_tasks', function (Blueprint $table) {
            $table->unsignedBigInteger('agreement_id')->after('upload_file')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('technique_tasks', function (Blueprint $table) {
            $table->dropColumn('agreement_id');
        });
    }
}
