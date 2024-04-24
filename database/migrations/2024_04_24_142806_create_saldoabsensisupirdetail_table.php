<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSaldoabsensisupirdetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('saldoabsensisupirdetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('absensi_id')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->unsignedBigInteger('trado_id')->nullable();
            $table->unsignedBigInteger('supir_id')->nullable();
            $table->longText('keterangan')->nullable();
            $table->double('uangjalan', 15, 2)->nullable();
            $table->unsignedBigInteger('absen_id')->nullable();
            $table->unsignedBigInteger('supirold_id')->nullable();
            $table->time('jam')->nullable();
            $table->integer('statussupirserap')->Length(11)->nullable();
            $table->integer('statusapprovaleditabsensi')->Length(11)->nullable();
            $table->string('userapprovaleditabsensi', 50)->nullable();
            $table->date('tglapprovaleditabsensi')->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby', 50)->nullable();
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
        Schema::dropIfExists('saldoabsensisupirdetail');
    }
}
