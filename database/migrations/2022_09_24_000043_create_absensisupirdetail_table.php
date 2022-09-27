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
            $table->unsignedBigInteger('absensi_id')->default(0);
            $table->string('nobukti', 50)->default('');
            $table->unsignedBigInteger('trado_id')->default(0);
            $table->unsignedBigInteger('supir_id')->default(0);
            $table->longText('keterangan')->default('');
            $table->double('uangjalan', 15,2)->default(0);
            $table->unsignedBigInteger('absen_id')->default(0);
            $table->time('jam')->default('');
            
            $table->string('modifiedby', 50)->default('');
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
