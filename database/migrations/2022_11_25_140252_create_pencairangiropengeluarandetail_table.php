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
            $table->unsignedBigInteger('pencairangiropengeluaran_id')->default('0');
            $table->string('nobukti',50)->default('');
            $table->unsignedBigInteger('alatbayar_id')->default('0');
            $table->string('nowarkat',50)->default('');
            $table->date('tgljatuhtempo')->default('1900/1/1');
            $table->double('nominal',15,2)->default('0');
            $table->string('coadebet',50)->default('');
            $table->string('coakredit',50)->default('');
            $table->longText('keterangan')->default('');
            $table->date('bulanbeban')->default('1900/1/1');
            $table->string('modifiedby',50)->default('');            
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
