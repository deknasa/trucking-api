<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePinjamandetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pinjamandetail', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->unsignedBigInteger('pinjaman_id')->default('0');
            $table->longText('keterangan')->default('');
            $table->unsignedBigInteger('supir_id')->default('0');
            $table->double('dp',15,2)->default('0');
            $table->double('nominal',15,2)->default('0');
            $table->double('nominalcicilan',15,2)->default('0');
            $table->string('modifiedby',50)->default('');
            $table->timestamps();

            $table->foreign('pinjaman_id')->references('id')->on('pinjamanheader')->onDelete('cascade');                                                            
            $table->foreign('supir_id')->references('id')->on('supir');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pinjamandetail');
    }
}
