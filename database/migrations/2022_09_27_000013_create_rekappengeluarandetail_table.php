<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateRekappengeluarandetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()

    {
        Schema::dropIfExists('rekappengeluarandetail');

        Schema::create('rekappengeluarandetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rekappengeluaran_id')->nullable();
            $table->string('nobukti',50)->nullable();
            $table->string('pengeluaran_nobukti',50)->nullable();
            $table->date('tgltransaksi')->nullable();
            $table->double('nominal',15,2)->nullable();
            $table->longText('keterangan')->nullable();
            $table->string('modifiedby',50)->nullable();            
            $table->timestamps();


            $table->foreign('rekappengeluaran_id', 'rekappengeluarandetail_rekappengeluaranheader_rekappengeluaran_id_foreign')->references('id')->on('rekappengeluaranheader')->onDelete('cascade');    
            $table->foreign('pengeluaran_nobukti', 'rekappengeluarandetail_pengeluaranheader_pengeluaran_nobukti_foreign')->references('nobukti')->on('pengeluaranheader');    

            
        });
        DB::statement("ALTER TABLE rekappengeluarandetail NOCHECK CONSTRAINT rekappengeluarandetail_pengeluaranheader_pengeluaran_nobukti_foreign");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rekappengeluarandetail');
    }
}
