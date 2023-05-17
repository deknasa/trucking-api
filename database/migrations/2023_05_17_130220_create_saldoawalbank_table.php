<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSaldoawalbankTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('saldoawalbank', function (Blueprint $table) {
            $table->id();
            $table->string('bulan',10)->nullable();          
            $table->integer('bank_id',50)->nullable();          
            $table->double('nominaldebet',15,2)->nullable();             
            $table->double('nominalkredit',15,2)->nullable();             
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
        Schema::dropIfExists('saldoawalbank');
    }
}
