<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJurnalumumdetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jurnalumumdetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('jurnalumum_id')->default('0');
            $table->string('nobukti',50)->default('');
            $table->date('tgl')->default('1900/1/1');
            $table->string('coa',50)->default('');
            $table->double('nominal',15,2)->default('0');
            $table->longText('keterangan')->default('');
            $table->string('modifiedby',50)->default('');
            $table->timestamps();

            $table->foreign('jurnalumum_id')->references('id')->on('jurnalumumheader')->onDelete('cascade');            
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
        Schema::dropIfExists('jurnalumumdetail');
    }
}
