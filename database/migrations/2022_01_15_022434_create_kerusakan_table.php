<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKerusakanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kerusakan', function (Blueprint $table) {
            $table->id();
            $table->string('keterangan',100)->default('');
            $table->integer('statusaktif')->length(11)->default('0');
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
        Schema::dropIfExists('kerusakan');
    }
}
