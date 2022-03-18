<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePengeluaranheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pengeluaranheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->date('tgl')->default('1900/1/1');
            $table->unsignedBigInteger('pelanggan_id')->default('0');
            $table->longText('keterangan')->default('');
            $table->integer('statusjenistransaksi')->Length(11)->default('0');
            $table->string('postingdari',50)->default('');
            $table->integer('statusapproval')->Length(11)->default('0');
            $table->string('dibayarke',250)->default('');
            $table->unsignedBigInteger('cabang_id')->default('0');
            $table->unsignedBigInteger('bank_id')->default('0');
            $table->string('userapproval',50)->default('');
            $table->date('tglapproval')->default('1900/1/1');
            $table->string('transferkeac',100)->default('');
            $table->string('transferkean',100)->default('');
            $table->string('transferkebank',100)->default('');
            $table->string('modifiedby',50)->default('');
            $table->timestamps();

            $table->foreign('pelanggan_id')->references('id')->on('pelanggan');
            $table->foreign('cabang_id')->references('id')->on('cabang');
            $table->foreign('bank_id')->references('id')->on('bank');


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pengeluaranheader');
    }
}
