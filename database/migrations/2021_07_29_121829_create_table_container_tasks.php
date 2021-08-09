<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableContainerTasks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('container_tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('title')->nullable();
            $table->enum('task_type', ['receive', 'ship']);
            $table->enum('trans_type', ['train', 'auto']);
            $table->enum('status', ['open', 'closed', 'failed']);
            $table->string('document_base')->nullable();
            $table->string('upload_file')->nullable();
            $table->json('container_ids')->nullable();
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
        Schema::dropIfExists('container_tasks');
    }
}
