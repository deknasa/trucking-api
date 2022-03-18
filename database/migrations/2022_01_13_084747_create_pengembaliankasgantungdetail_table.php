<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePengembaliankasgantungdetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pengembaliankasgantungdetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pengembaliankasgantung_id')->default('0');
            $table->string('nobukti',50)->default('');
            $table->double('nominal',15,2)->default('0');
            $table->string('coa',50)->default('');
            $table->longText('keterangan')->default('');
            $table->string('modifiedby',50)->default('');
            $table->string('kasgantung_nobukti',50)->default('');
            $table->timestamps();

            $table->foreign('pengembaliankasgantung_id')->references('id')->on('pengembaliankasgantungheader')->onDelete('cascade');                                                            
            $table->foreign('kasgantung_nobukti')->references('nobukti')->on('kasgantungheader');


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pengembaliankasgantungdetail');
    }
}
