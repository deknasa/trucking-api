<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePelunasanpiutangheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pelunasanpiutangheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->default('');
            $table->date('tgl')->default('1900/1/1');
            $table->longText('keterangan')->default('');
            $table->unsignedBigInteger('bank_id')->default('0');
            $table->unsignedBigInteger('emkl_id')->default('0');
            $table->unsignedBigInteger('cabang_id')->default('0');
            $table->string('modifiedby',50)->default('');
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
        Schema::dropIfExists('pelunasanpiutangheader');
    }
}
