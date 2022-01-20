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
        Schema::create('absentrado', function (Blueprint $table) {
            $table->id();
            $table->string('kodeabsen', 100)->default('');
            $table->string('keterangan', 250)->default('');
            $table->integer('statusaktif')->length(11);
            $table->string('modifiedby', 30)->default('');
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
