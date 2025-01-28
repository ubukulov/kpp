<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTablePermits extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permits', function (Blueprint $table) {
            $table->id();
            $table->string('mark_car')->nullable();
            $table->string('gov_number')->nullable();
            $table->string('tex_number')->nullable();
            $table->string('company')->nullable();
            $table->string('pr_number')->nullable();
            $table->integer('operation_type')->default(1);
            $table->timestamp('date_in')->nullable();
            $table->timestamp('date_out')->nullable();
            $table->string('last_name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('sur_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('ud_number')->nullable();
            $table->string('path_docs_fac')->nullable();
            $table->string('path_docs_back')->nullable();
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
        Schema::dropIfExists('permits');
    }
}
