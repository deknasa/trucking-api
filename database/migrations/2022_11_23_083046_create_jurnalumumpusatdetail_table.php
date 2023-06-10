<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateJurnalumumpusatdetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('jurnalumumpusatdetail');

        Schema::create('jurnalumumpusatdetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('jurnalumumpusat_id')->nullable();
            $table->string('nobukti',50)->nullable();
            $table->date('tglbukti')->nullable();
            $table->string('coa',50)->nullable();
            $table->string('coamain',50)->nullable();
            $table->double('nominal',15,2)->nullable();
            $table->longText('keterangan')->nullable();
            $table->integer('baris')->length(11)->nullable();
            $table->string('modifiedby',50)->nullable();            
            $table->timestamps();

            $table->foreign('jurnalumumpusat_id', 'jurnalumumpusatdetail_jurnalumumpusatheader_jurnalumumpusat_id_foreign')->references('id')->on('jurnalumumpusatheader')->onDelete('cascade');  
            $table->foreign('coa', 'jurnalumumpusatdetail_akunpusat_coa_foreign')->references('coa')->on('akunpusat');  

        });

        DB::statement("ALTER TABLE jurnalumumpusatdetail NOCHECK CONSTRAINT jurnalumumpusatdetail_akunpusat_coa_foreign");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('jurnalumumpusatdetail');
    }
}
