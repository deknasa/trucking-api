<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDepositoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deposito', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->date('tgl')->default('1900/1/1');
            $table->longText('keterangan')->default('');
            $table->unsignedBigInteger('supir_id')->default('0');
            $table->unsignedBigInteger('bank_id')->default('0');
            $table->double('nominal',15,2)->default('0');
            $table->string('coa',50)->default('');
            $table->string('nobuktikasmasuk',50)->default('');
            $table->date('tglkasmasuk',50)->default('1900/1/1');
            $table->string('modifiedby',50)->default('');
            $table->timestamps();

            $table->foreign('supir_id')->references('id')->on('supir');
            $table->foreign('bank_id')->references('id')->on('bank');
            $table->foreign('coa')->references('coa')->on('akunpusat');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('deposito');
    }
}
