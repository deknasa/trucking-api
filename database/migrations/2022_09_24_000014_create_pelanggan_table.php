<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePelangganTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()

    
    {

        Schema::dropIfExists('pelanggan');
        
        Schema::create('pelanggan', function (Blueprint $table) {
            $table->id();
            $table->string('kodepelanggan',50)->nullable();
            $table->string('namapelanggan',100)->nullable();
            $table->string('namakontak',1000)->nullable();
            $table->longText('keterangan')->nullable();
            $table->string('telp',100)->nullable();
            $table->string('alamat',200)->nullable();
            $table->string('alamat2',200)->nullable();
            $table->string('kota',200)->nullable();
            $table->string('kodepos',50)->nullable();
            $table->integer('statusaktif')->length(11)->nullable();                
            $table->unsignedBigInteger('tas_id')->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby',50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pelanggan');
    }
}
