<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStokafkirTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stokafkir', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->default('');
            $table->date('tgl')->default('1900/1/1');
            $table->unsignedBigInteger('stok_id')->default('0');
            $table->integer('statusban')->length(11)->default('0');
            $table->integer('vulkanisirke')->length(11)->default('0');
            $table->integer('statusbanafkir')->length(11)->default('0');
            $table->longText('keterangan')->default('');
            $table->string('klaim_nobukti',50)->default('');
            $table->string('pinjaman_nobukti',50)->default('');
            $table->integer('jumlahhariaki')->length(11)->default('0');
            $table->string('modifiedby',50)->default('');
            $table->timestamps();

            $table->foreign('stok_id')->references('id')->on('stok');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stokafkir');
    }
}
