<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePengembaliankasgantungheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('pengembaliankasgantungheader');
        
        Schema::create('pengembaliankasgantungheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->date('tglbukti')->default('1900/1/1');
            $table->unsignedBigInteger('pelanggan_id')->default('0');
            $table->longText('keterangan')->default('');
            $table->unsignedBigInteger('bank_id')->default('0');
            $table->date('tgldari')->default('1900/1/1');
            $table->date('tglsampai')->default('1900/1/1');
            $table->string('penerimaan_nobukti',50)->default('');
            $table->string('coakasmasuk',50)->default('');
            $table->string('postingdari',50)->default('');
            $table->date('tglkasmasuk')->default('1900/1/1');
            $table->unsignedBigInteger('statusformat')->default(0);
            $table->string('modifiedby',50)->default('');
            $table->timestamps();


            $table->foreign('pelanggan_id', 'pengembaliankasgantungheader_pelanggan_pelanggan_id_foreign')->references('id')->on('pelanggan');    
            $table->foreign('bank_id', 'pengembaliankasgantungheader_bank_bank_id_foreign')->references('id')->on('bank');    
            $table->foreign('penerimaan_nobukti', 'pengembaliankasgantungheader_penerimaanheader_penerimaan_nobukti_foreign')->references('nobukti')->on('penerimaanheader');    
            $table->foreign('coakasmasuk', 'pengembaliankasgantungheader_akunpusat_coakasmasuk_foreign')->references('coa')->on('akunpusat');    

        });

        
        DB::statement("ALTER TABLE pengembaliankasgantungheader NOCHECK CONSTRAINT pengembaliankasgantungheader_pelanggan_pelanggan_id_foreign");
        DB::statement("ALTER TABLE pengembaliankasgantungheader NOCHECK CONSTRAINT pengembaliankasgantungheader_bank_bank_id_foreign");
        DB::statement("ALTER TABLE pengembaliankasgantungheader NOCHECK CONSTRAINT pengembaliankasgantungheader_penerimaanheader_penerimaan_nobukti_foreign");
        DB::statement("ALTER TABLE pengembaliankasgantungheader NOCHECK CONSTRAINT pengembaliankasgantungheader_akunpusat_coakasmasuk_foreign");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pengembaliankasgantungheader');
    }
}
