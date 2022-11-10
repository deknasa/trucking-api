<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHariliburTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('harilibur', function (Blueprint $table) {
            $table->id();
            $table->date('tgl')->Default('1900/1/1');
            $table->longText('keterangan')->Default('');
            $table->integer('statusaktif')->length(11)->default(0);
            $table->string('modifiedby',50)->Default('');            
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
        Schema::dropIfExists('harilibur');
    }
}
