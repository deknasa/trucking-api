<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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
            $table->unsignedBigInteger('bank_id')->nullable();     
            $table->double('nominaldebet',15,2)->nullable();             
            $table->double('nominalkredit',15,2)->nullable();             
            $table->timestamps();

            $table->foreign('bank_id', 'saldoawalbank_bank_bank_id_foreign')->references('id')->on('bank');

        });

        DB::statement("ALTER TABLE saldoawalbank NOCHECK CONSTRAINT saldoawalbank_bank_bank_id_foreign");

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
