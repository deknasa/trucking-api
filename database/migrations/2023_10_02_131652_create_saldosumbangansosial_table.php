<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSaldosumbangansosialTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('saldosumbangansosial', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_detail')->nullable();
            $table->string('noinvoice_detail',50)->nullable();
            $table->string('nojobtrucking_detail',50)->nullable();
            $table->unsignedBigInteger('container_id')->nullable();
            $table->date('tgl_bukti')->nullable();
            $table->longText('info')->nullable();            
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
        Schema::dropIfExists('saldosumbangansosial');
    }
}
