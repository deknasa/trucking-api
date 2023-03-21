<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMenuTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menu', function (Blueprint $table) {
            $table->id();
            $table->string('menuname',50)->nullable();
            $table->integer('menuseq')->length(11)->nullable();
            $table->integer('menuparent')->length(11)->nullable();
            $table->string('menuicon',50)->nullable();
            $table->unsignedBigInteger('aco_id')->nullable();
            $table->string('link',2000)->nullable();
            $table->string('menuexe',200)->nullable();
            $table->string('menukode',50)->unique()->nullable();
            $table->string('modifiedby',50)->nullable();
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
        Schema::dropIfExists('menu');
    }
}
