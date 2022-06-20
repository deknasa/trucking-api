<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStokTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stok', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('jenistrado_id')->default('0');
            $table->unsignedBigInteger('kelompok_id')->default('0');
            $table->unsignedBigInteger('subkelompok_id')->default('0');
            $table->unsignedBigInteger('kategori_id')->default('0');
            $table->unsignedBigInteger('merk_id')->default('0');
            $table->integer('conv1')->length(11)->default('0');
            $table->integer('conv2')->length(11)->default('0');
            $table->string('namastok',200)->default('');
            $table->integer('statusaktif')->length(11)->default('0');
            $table->double('qtymin',15,2)->default('0');
            $table->double('qtymax',15,2)->default('0');
            $table->double('hrgbelimax',15,2)->default('0');
            $table->integer('statusban')->length(11)->default('0');
            $table->string('ukuranban',200)->default('');
            $table->longText('keterangan')->default('');
            $table->longText('gambar')->default('');
            $table->longText('namaterpusat')->default('');
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
        Schema::dropIfExists('stok');
    }
}
