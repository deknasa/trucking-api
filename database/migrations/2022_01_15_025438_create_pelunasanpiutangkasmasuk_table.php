<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePelunasanpiutangkasmasukTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pelunasanpiutangkasmasuk', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pelunasanpiutang_id')->default('0');
            $table->string('nobukti',50)->default('');
            $table->date('tgl')->default('1900/1/1');
            $table->double('nominal',15,2)->default('');
            $table->integer('statuscair')->length(11)->default('0');
            $table->date('tglcair')->default('1900/1/1');
            $table->string('nowarkat',50)->default('');
            $table->string('bankwarkat',50)->default('');
            $table->longText('keterangan')->default('');
            $table->string('coadebet',50)->default('');
            $table->string('coakredit',50)->default('');
            $table->string('postingdari',50)->default('');
            $table->date('tgljt')->default('1900/1/1');
            $table->unsignedBigInteger('bank_id')->default('0');
            $table->unsignedBigInteger('bankpelanggan_id')->default('0');
            $table->string('modifiedby',50)->default('');
            $table->timestamps();

            $table->foreign('pelunasanpiutang_id')->references('id')->on('pelunasanpiutangheader')->onDelete('cascade');                                    
            $table->foreign('bank_id')->references('id')->on('bank');
            $table->foreign('bankpelanggan_id')->references('id')->on('bankpelanggan');
            $table->foreign('coadebet')->references('coa')->on('akunpusat');
            $table->foreign('coakredit')->references('coa')->on('akunpusat');

            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pelunasanpiutangkasmasuk');
    }
}
