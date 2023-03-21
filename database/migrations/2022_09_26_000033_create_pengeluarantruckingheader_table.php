<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePengeluarantruckingheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('pengeluarantruckingheader');

        Schema::create('pengeluarantruckingheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->date('tglbukti')->nullable();            
            $table->longText('keterangan')->nullable();            
            $table->unsignedBigInteger('pengeluarantrucking_id')->nullable();
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->integer('statusposting')->length(11)->nullable();
            $table->string('coa',50)->nullable();
            $table->string('pengeluaran_nobukti',50)->nullable();
            $table->unsignedBigInteger('statusformat')->nullable();            
            $table->integer('statuscetak')->Length(11)->nullable();
            $table->string('userbukacetak',50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->Length(11)->nullable();
            $table->string('modifiedby',50)->nullable();

            $table->timestamps();

              
            $table->foreign('pengeluarantrucking_id', 'pengeluarantruckingheader_pengeluarantrucking_pengeluarantrucking_id_foreign')->references('id')->on('pengeluarantrucking');   
            $table->foreign('bank_id', 'pengeluarantruckingheader_bank_bank_id_foreign')->references('id')->on('bank');   
            $table->foreign('pengeluaran_nobukti', 'pengeluarantruckingheader_pengeluaranheader_penerimaan_nobukti_foreign')->references('nobukti')->on('pengeluaranheader');   
            $table->foreign('coa', 'pengeluarantruckingheader_akunpusat_coa_foreign')->references('coa')->on('akunpusat');  
        });

        DB::statement("ALTER TABLE pengeluarantruckingheader NOCHECK CONSTRAINT pengeluarantruckingheader_pengeluarantrucking_pengeluarantrucking_id_foreign");
        DB::statement("ALTER TABLE pengeluarantruckingheader NOCHECK CONSTRAINT pengeluarantruckingheader_bank_bank_id_foreign");
        DB::statement("ALTER TABLE pengeluarantruckingheader NOCHECK CONSTRAINT pengeluarantruckingheader_pengeluaranheader_penerimaan_nobukti_foreign");
        DB::statement("ALTER TABLE pengeluarantruckingheader NOCHECK CONSTRAINT pengeluarantruckingheader_akunpusat_coa_foreign");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pengeluarantruckingheader');
    }
}
