<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePenerimaandetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('penerimaandetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('penerimaan_id')->default('0');
            $table->string('nobukti',50)->default('');
            $table->string('nowarkat',50)->default('');
            $table->date('tgljt')->default('1900/1/1');
            $table->double('nominal',15,2)->default('0');
            $table->string('coadebet',50)->default('');
            $table->string('coakredit',50)->default('');
            $table->longText('keterangan')->default('');
            $table->unsignedBigInteger('bank_id')->default('0');
            $table->unsignedBigInteger('pelanggan_id')->default('0');
            $table->string('noinvoice',50)->default('');
            $table->unsignedBigInteger('bankpelanggan_id')->default('0');
            $table->string('jenisbiaya',50)->default('');
            $table->string('pelunasan_nobukti',50)->default('');
            $table->date('blnbeban')->default('1900/1/1');
            $table->string('modifiedby',50)->default('');
            $table->timestamps();

            $table->foreign('penerimaan_id')->references('id')->on('penerimaanheader')->onDelete('cascade');                                                
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('penerimaandetail');
    }
}
