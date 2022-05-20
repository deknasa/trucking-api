<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePenerimaangiropelunasanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('penerimaangiropelunasan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('penerimaangiro_id')->default('0');
            $table->string('nobukti',50)->default('');
            $table->string('penerimaanpiutang_nobukti',50)->default('');
            $table->date('tglterima')->default('1900/1/1');
            $table->double('nominal',15,2)->default('0');
            $table->string('modifiedby',50)->default('');
            $table->timestamps();

            $table->foreign('penerimaangiro_id')->references('id')->on('penerimaangiroheader')->onDelete('cascade');                                                
            $table->foreign('penerimaanpiutang_nobukti')->references('nobukti')->on('pelunasanpiutangheader');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('penerimaangiropelunasan');
    }
}
