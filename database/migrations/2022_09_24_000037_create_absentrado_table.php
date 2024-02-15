<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateabsentradoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::dropIfExists('absentrado');
        
        Schema::create('absentrado', function (Blueprint $table) {
            $table->id();
            $table->string('kodeabsen', 100)->nullable();
            $table->longText('keterangan')->nullable();
            $table->integer('statusaktif')->length(11);
            $table->longText('memo')->nullable();
            $table->unsignedBigInteger('tas_id')->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby', 30)->nullable();
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
        Schema::dropIfExists('absentrado');
    }
}
