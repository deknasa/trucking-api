<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateabsensisupirdetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('absensisupirdetail');

        Schema::create('absensisupirdetail', function (Blueprint $table) {
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
            $table->integer('statusjeniskendaraan')->Length(11)->nullable();
            $table->integer('statussupirserap')->Length(11)->nullable();
            $table->integer('statusapprovaleditabsensi')->Length(11)->nullable();
            $table->string('userapprovaleditabsensi', 50)->nullable();
            $table->date('tglapprovaleditabsensi')->nullable();
            $table->integer('statustambahantrado')->Length(11)->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby', 50)->nullable();
            
            $table->timestamps();


            $table->foreign('absensi_id', 'absensisupirdetail_absensisupirheader_absensi_id_foreign')->references('id')->on('absensisupirheader')->onDelete('cascade');
            $table->foreign('trado_id', 'absensisupirdetail_trado_trado_id_foreign')->references('id')->on('trado');
            $table->foreign('supir_id', 'absensisupirdetail_supir_supir_id_foreign')->references('id')->on('supir');
            $table->foreign('absen_id', 'absensisupirdetail_absentrado_absen_id_foreign')->references('id')->on('absentrado');
        });

        DB::statement("ALTER TABLE absensisupirdetail NOCHECK CONSTRAINT absensisupirdetail_trado_trado_id_foreign");
        DB::statement("ALTER TABLE absensisupirdetail NOCHECK CONSTRAINT absensisupirdetail_supir_supir_id_foreign");
        DB::statement("ALTER TABLE absensisupirdetail NOCHECK CONSTRAINT absensisupirdetail_absentrado_absen_id_foreign");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('absensisupirdetail');
    }
}
