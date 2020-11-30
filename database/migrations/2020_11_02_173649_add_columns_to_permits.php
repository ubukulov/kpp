<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToPermits extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('permits', function (Blueprint $table) {
            $table->integer('cat_tc_id')->after('path_docs_back')->default(0);
            $table->integer('body_type_id')->after('path_docs_back')->default(0);
            $table->integer('is_driver')->after('path_docs_back')->default(0);
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
            $table->dropColumn(['cat_tc_id', 'body_type_id', 'is_driver']);
        });
    }
}
