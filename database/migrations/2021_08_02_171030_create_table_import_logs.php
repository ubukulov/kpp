<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableImportLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('import_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('container_task_id');
            $table->unsignedBigInteger('user_id');
            $table->string('container_number')->nullable();
            $table->text('comments')->nullable();
            $table->enum('status', ['ok', 'not']);
            $table->timestamp('import_date')->useCurrent();
            $table->string('ip')->nullable();
            $table->timestamps();

            $table->foreign('container_task_id')
                ->references('id')->on('container_tasks')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')->on('users')
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
        Schema::dropIfExists('import_logs');
    }
}
