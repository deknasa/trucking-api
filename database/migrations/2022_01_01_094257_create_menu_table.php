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
            $table->string('menuname',50)->default('');
            $table->integer('menuseq')->length(11)->default('0');
            $table->integer('menuparent')->length(11)->default('0');
            $table->string('menuicon',50)->default('');
            $table->unsignedBigInteger('aco_id')->default('0');
            $table->string('link',2000)->default('');
            $table->string('menuexe',200)->default('');
            $table->string('menukode',50)->unique()->default('');
            $table->string('modifiedby',50)->default('');
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
