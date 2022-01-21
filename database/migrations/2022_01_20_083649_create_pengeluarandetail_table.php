<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePengeluarandetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pengeluarandetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pengeluaran_id')->default('0');
            $table->string('nobukti',50)->default('');
            $table->unsignedBigInteger('alatbayar_id')->default('0');
            $table->string('nowarkat',50)->default('');
            $table->date('tgljt')->default('1900/1/1');
            $table->double('nominal',15,2)->default('0');
            $table->string('coadebet',50)->default('');
            $table->string('coakredit',50)->default('');
            $table->longText('keterangan')->default('');
            $table->unsignedBigInteger('bank_id')->default('0');
            $table->string('noinvoice',50)->default('');
            $table->integer('statusedit')->longText(11)->default('0');
            $table->date('bulanbeban')->default('1900/1/1');
            $table->string('modifiedby',50)->default('');
            $table->timestamps();

            $table->foreign('pengeluaran_id')->references('id')->on('pengeluaranheader')->onDelete('cascade');                                                            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pengeluarandetail');
    }
}
