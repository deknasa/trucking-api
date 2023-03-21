<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateJurnalumumdetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::dropIfExists('jurnalumumdetail');

        Schema::create('jurnalumumdetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('jurnalumum_id')->nullable();
            $table->string('nobukti',50)->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('coa',50)->nullable();
            $table->double('nominal',15,2)->nullable();
            $table->longText('keterangan')->nullable();
            $table->integer('baris')->length(11)->nullable();
            $table->string('modifiedby',50)->nullable();
            $table->timestamps();


            $table->foreign('jurnalumum_id', 'jurnalumumdetail_jurnalumumheader_jurnalumum_id_foreign')->references('id')->on('jurnalumumheader')->onDelete('cascade');  
            $table->foreign('coa', 'jurnalumumdetail_akunpusat_coa_foreign')->references('coa')->on('akunpusat');  

        });
        DB::statement("ALTER TABLE jurnalumumdetail NOCHECK CONSTRAINT jurnalumumdetail_akunpusat_coa_foreign");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('jurnalumumdetail');
    }
}
