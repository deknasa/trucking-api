<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePinjamanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pinjaman', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->date('tgl')->default('1900/1/1');
            $table->longText('keterangan')->default('');
            $table->unsignedBigInteger('supir_id')->default('0');
            $table->unsignedBigInteger('bank_id')->default('0');
            $table->double('dp',15,2)->default('0');
            $table->double('nominal',15,2)->default('0');
            $table->double('nominalcicilan',15,2)->default('0');
            $table->string('coa',50)->default('');
            $table->string('nobuktikaskeluar',50)->default('');
            $table->date('tglkaskeluar',50)->default('1900/1/1');
            $table->integer('notpost')->length(11)->default('0');
            $table->string('modifiedby',50)->default('');
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
        Schema::dropIfExists('pinjaman');
    }
}
