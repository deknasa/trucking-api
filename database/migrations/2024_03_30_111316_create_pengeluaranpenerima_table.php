<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePengeluaranpenerimaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pengeluaranpenerima', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pengeluaran_id')->nullable();
            $table->string('nobukti',50)->nullable();            
            $table->unsignedBigInteger('penerima_id')->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby',50)->nullable();
            $table->timestamps();

            $table->foreign('pengeluaran_id', 'pengeluaranpenerima_pengeluaranheader_pengeluaran_id_foreign')->references('id')->on('pengeluaranheader')->onDelete('cascade');       

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pengeluaranpenerima');
    }
}
