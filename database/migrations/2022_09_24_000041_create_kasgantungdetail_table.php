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
            $table->unsignedBigInteger('kasgantung_id')->default('0');
            $table->string('nobukti',50)->default('');
            $table->double('nominal',15,2)->default('0');
            $table->string('coa',50)->default('');
            $table->longText('keterangan')->default('');
            $table->string('modifiedby',50)->default('');
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
