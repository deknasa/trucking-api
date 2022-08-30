<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKoreksistokheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('koreksistokheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->date('tglbukti',50)->default('1900/1/1');
            $table->longText('keterangan')->default('');
            $table->unsignedBigInteger('trado_id')->default('0');
            $table->unsignedBigInteger('gudang_id')->default('0');
            $table->string('tipe',50)->default('');
            $table->string('modifiedby',50)->default('');
            $table->timestamps();

            $table->foreign('trado_id')->references('id')->on('trado');
            $table->foreign('gudang_id')->references('id')->on('gudang');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('koreksistokheader');
    }
}
