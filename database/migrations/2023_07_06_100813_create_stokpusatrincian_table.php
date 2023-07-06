<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStokpusatrincianTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stokpusatrincian', function (Blueprint $table) {
            $table->id();
            $table->string('namastok',200)->nullable();
            $table->unsignedBigInteger('kelompok_id')->nullable();
            $table->unsignedBigInteger('stok_id')->nullable();
            $table->unsignedBigInteger('cabang_id')->nullable();
            $table->longText('gambar')->nullable();
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
        Schema::dropIfExists('stokpusatrincian');
    }
}
