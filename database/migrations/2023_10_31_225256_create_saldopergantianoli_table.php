<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSaldopergantianoliTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('saldopergantianoli', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trado_id')->nullable();
            $table->date('tglpergantian')->nullable();
            $table->unsignedBigInteger('statusreminder')->nullable();
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
        Schema::dropIfExists('saldopergantianoli');
    }
}
