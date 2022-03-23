<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubkelompokTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subkelompok', function (Blueprint $table) {
            $table->id();
            $table->string('kodesubkelompok',50)->default('');
            $table->longText('keterangan')->default('');
            $table->unsignedBigInteger('kelompok_id')->default('');
            $table->integer('statusaktif')->length(11)->default('');
            $table->longText('modifiedby',50)->default('');
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
        Schema::dropIfExists('subkelompok');
    }
}
