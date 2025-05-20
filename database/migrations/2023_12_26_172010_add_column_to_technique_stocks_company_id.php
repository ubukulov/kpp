<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToTechniqueStocksCompanyId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('technique_stocks', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->after('technique_place_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('technique_stocks', function (Blueprint $table) {
            $table->dropColumn('company_id');
        });
    }
}
