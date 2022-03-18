<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKasgantungdetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kasgantungdetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kasgantung_id')->default('0');
            $table->string('nobukti',50)->default('');
            $table->double('nominal',15,2)->default('0');
            $table->string('coa',50)->default('');
            $table->longText('keterangan')->default('');
            $table->string('modifiedby',50)->default('');
            $table->timestamps();

            $table->foreign('kasgantung_id')->references('id')->on('kasgantungheader')->onDelete('cascade');            
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
        Schema::dropIfExists('kasgantungdetail');
    }
}
