<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePenerimaanstokpenambahannilaiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('penerimaanstokpenambahannilai', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('penerimaanstokheader_id');
            $table->string('nobukti',50)->nullable();
            $table->unsignedBigInteger('stok_id');
            $table->double('qty', 15,2)->nullable();
            $table->double('harga', 15,2)->nullable();            
            $table->string('penerimaanstok_nobukti',50)->nullable();
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
        Schema::dropIfExists('penerimaanstokpenambahannilai');
    }
}
