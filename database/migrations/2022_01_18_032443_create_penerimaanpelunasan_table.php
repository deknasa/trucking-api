<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePenerimaanpelunasanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('penerimaanpelunasan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('penerimaan_id')->default('0');
            $table->string('nobukti',50)->default('');
            $table->string('pelunasan_nobukti',50)->default('');
            $table->date('tglterima')->default('1900/1/1');
            $table->double('nominal',15,2)->default('0');
            $table->string('modifiedby',50)->default('');
            $table->timestamps();

            $table->foreign('penerimaan_id')->references('id')->on('penerimaanheader')->onDelete('cascade');                                                

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('penerimaanpelunasan');
    }
}
