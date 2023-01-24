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
            $table->string('kodepelanggan',50)->default('');
            $table->string('namapelanggan',100)->default('');
            $table->longText('keterangan')->default('');
            $table->string('telp',100)->default('');
            $table->string('alamat',200)->default('');
            $table->string('alamat2',200)->default('');
            $table->string('kota',200)->default('');
            $table->string('kodepos',50)->default('');
            $table->string('modifiedby',50)->default('');
            $table->integer('statusaktif')->length(11)->default(0);                
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
