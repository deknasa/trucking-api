<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePenerimaantruckingheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('penerimaantruckingheader');

        Schema::create('penerimaantruckingheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->date('tglbukti')->default('1900/1/1');            
            $table->unsignedBigInteger('penerimaantrucking_id')->default(0);
            $table->longText('keterangan')->default('');
            $table->unsignedBigInteger('bank_id')->default('0');
            $table->string('coa',50)->default('');
            $table->string('penerimaan_nobukti',50)->default('');
            $table->unsignedBigInteger('statusformat')->default(0);
            $table->string('modifiedby',50)->default('');
            $table->timestamps();

            $table->foreign('penerimaantrucking_id', 'penerimaantruckingheader_penerimaantrucking_penerimaantrucking_id_foreign')->references('id')->on('penerimaantrucking');   
            $table->foreign('bank_id', 'penerimaantruckingheader_bank_bank_id_foreign')->references('id')->on('bank');   
            $table->foreign('penerimaan_nobukti', 'penerimaantruckingheader_penerimaanheader_penerimaan_nobukti_foreign')->references('nobukti')->on('penerimaanheader');   
            $table->foreign('coa', 'penerimaantruckingheader_akunpusat_coa_foreign')->references('coa')->on('akunpusat');   

        });

        DB::statement("ALTER TABLE penerimaantruckingheader NOCHECK CONSTRAINT penerimaantruckingheader_penerimaantrucking_penerimaantrucking_id_foreign");
        DB::statement("ALTER TABLE penerimaantruckingheader NOCHECK CONSTRAINT penerimaantruckingheader_bank_bank_id_foreign");
        DB::statement("ALTER TABLE penerimaantruckingheader NOCHECK CONSTRAINT penerimaantruckingheader_penerimaanheader_penerimaan_nobukti_foreign");
        DB::statement("ALTER TABLE penerimaantruckingheader NOCHECK CONSTRAINT penerimaantruckingheader_akunpusat_coa_foreign");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('penerimaantruckingheader');
    }
}
