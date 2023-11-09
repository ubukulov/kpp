<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToContainerTasks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('container_tasks', function (Blueprint $table) {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE container_tasks CHANGE COLUMN status status ENUM('open', 'closed', 'failed', 'waiting')");
            $table->enum('kind', [
                'common', 'automatic'
            ])->after('print_count');
            $table->integer('child_id')->default(0)->after('kind');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('container_tasks', function (Blueprint $table) {
            $table->dropColumn(['kind', 'child_id']);
        });
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE container_tasks CHANGE COLUMN status status ENUM('open', 'closed', 'failed')");
    }
}
