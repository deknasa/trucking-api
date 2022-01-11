<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTbankTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbank', function (Blueprint $table) {
            $table->id();
            $table->string('kbank', 50)->default('');
            $table->string('nbank', 50)->default('');
            $table->string('coa', 50)->default('');
            $table->string('tipe', 50)->default('');
            $table->integer('statusaktif')->length(11)->default(0);
            $table->string('modifiedby', 50)->default('');
            $table->integer('kodepenerimaan')->length(11)->default(0);
            $table->integer('kodepengeluaran')->length(11)->default(0);
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
        Schema::dropIfExists('tbank');
    }
}
