<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnStateToImportLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('import_logs', function (Blueprint $table) {
            $table->enum('state', [
                'not_posted', 'posted', 'not_selected', 'selected', 'issued'
            ])->after('ip');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('import_logs', function (Blueprint $table) {
            $table->dropColumn('state');
        });
    }
}
