<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRekappengeluarandetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()

    {
        Schema::create('rekappengeluarandetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rekappengeluaran_id')->default('0');
            $table->string('nobukti',50)->default('');
            $table->string('pengeluaran_nobukti',50)->default('');
            $table->date('tgltransaksi')->default('1900/1/1');
            $table->double('nominal',15,2)->default('0');
            $table->longText('keterangan')->default('');
            $table->string('modifiedby',50)->default('');            
            $table->timestamps();

            $table->foreign('rekappengeluaran_id')->references('id')->on('rekappengeluaranheader')->onDelete('cascade');                                                
            $table->foreign('pengeluaran_nobukti')->references('nobukti')->on('pengeluaranheader');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rekappengeluarandetail');
    }
}
