<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePencairangiropengeluarandetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::dropIfExists('pencairangiropengeluarandetail');

        Schema::create('pencairangiropengeluarandetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pencairangiropengeluaran_id')->nullable();
            $table->string('nobukti',50)->nullable();
            $table->unsignedBigInteger('alatbayar_id')->nullable();
            $table->string('nowarkat',50)->nullable();
            $table->date('tgljatuhtempo')->nullable();
            $table->double('nominal',15,2)->nullable();
            $table->string('coadebet',50)->nullable();
            $table->string('coakredit',50)->nullable();
            $table->longText('keterangan')->nullable();
            $table->date('bulanbeban')->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby',50)->nullable();            
            $table->timestamps();

            $table->foreign('pencairangiropengeluaran_id', 'pencairangiropengeluarandetail_pencairangiroppengeluaranheader_pencairangiropengeluaran_id_foreign')->references('id')->on('pencairangiropengeluaranheader')->onDelete('cascade');       
            $table->foreign('alatbayar_id', 'pencairangiropengeluarandetail_alatbayar_id_alatbayar_id_foreign')->references('id')->on('alatbayar');
            $table->foreign('coadebet', 'pencairangiropengeluarandetail_akunpusat_coadebet_foreign')->references('coa')->on('akunpusat');
            $table->foreign('coakredit', 'pencairangiropengeluarandetail_akunpusat_coakredit_foreign')->references('coa')->on('akunpusat');
        });

        DB::statement("ALTER TABLE pencairangiropengeluarandetail NOCHECK CONSTRAINT pencairangiropengeluarandetail_alatbayar_id_alatbayar_id_foreign");
        DB::statement("ALTER TABLE pencairangiropengeluarandetail NOCHECK CONSTRAINT pencairangiropengeluarandetail_akunpusat_coadebet_foreign");
        DB::statement("ALTER TABLE pencairangiropengeluarandetail NOCHECK CONSTRAINT pencairangiropengeluarandetail_akunpusat_coakredit_foreign");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pencairangiropengeluarandetail');
    }
}
