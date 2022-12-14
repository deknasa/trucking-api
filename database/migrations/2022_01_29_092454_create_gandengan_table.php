<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGandenganTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gandengan', function (Blueprint $table) {
            $table->id();
            $table->string('kodegandengan', 300)->default('');
            $table->string('keterangan', 300)->default('');
            $table->integer('statusaktif')->length(11)->default(0);
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
        Schema::dropIfExists('gandengan');
    }
}
