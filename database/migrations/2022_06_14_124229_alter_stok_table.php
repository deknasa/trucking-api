<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterStokTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stok', function (Blueprint $table) {
            $table->foreign('jenistrado_id')->references('id')->on('jenistrado');
            $table->foreign('kelompok_id')->references('id')->on('kelompok');
            $table->foreign('subkelompok_id')->references('id')->on('subkelompok');
            $table->foreign('kategori_id')->references('id')->on('kategori');
            $table->foreign('merk_id')->references('id')->on('merk');
       });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
