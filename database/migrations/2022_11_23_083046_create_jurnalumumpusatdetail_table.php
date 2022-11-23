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
            $table->unsignedBigInteger('jurnalumumpusat_id')->default('0');
            $table->string('nobukti',50)->default('');
            $table->date('tglbukti')->default('1900/1/1');
            $table->string('coa',50)->default('');
            $table->double('nominal',15,2)->default('0');
            $table->longText('keterangan')->default('');
            $table->integer('baris')->length(11)->default('0');
            $table->string('modifiedby',50)->default('');            
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
