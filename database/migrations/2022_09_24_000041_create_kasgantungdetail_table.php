<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateKasgantungdetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::dropIfExists('kasgantungdetail');

        Schema::create('kasgantungdetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kasgantung_id')->nullable();
            $table->string('nobukti',50)->nullable();
            $table->double('nominal',15,2)->nullable();
            $table->string('coa',50)->nullable();
            $table->longText('keterangan')->nullable();
            $table->string('modifiedby',50)->nullable();
            $table->timestamps();


            $table->foreign('kasgantung_id', 'kasgantungdetail_kasgantungheader_kasgantung_id_foreign')->references('id')->on('kasgantungheader')->onDelete('cascade');    
            $table->foreign('coa', 'kasgantungdetail_akunpusat_coa_foreign')->references('coa')->on('akunpusat');



        });

        DB::statement("ALTER TABLE kasgantungdetail NOCHECK CONSTRAINT kasgantungdetail_akunpusat_coa_foreign");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kasgantungdetail');
    }
}
