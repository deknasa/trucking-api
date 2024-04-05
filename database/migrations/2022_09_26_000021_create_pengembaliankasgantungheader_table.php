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
            $table->date('tglbukti')->nullable();
            $table->longText('keterangan')->nullable();
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->unsignedBigInteger('pelanggan_id')->nullable();
            $table->date('tgldari')->nullable();
            $table->date('tglsampai')->nullable();
            $table->string('penerimaan_nobukti',50)->nullable();
            $table->string('coakasmasuk',50)->nullable();
            $table->string('postingdari',50)->nullable();
            $table->date('tglkasmasuk')->nullable();
            $table->unsignedBigInteger('statusformat')->nullable();
            $table->integer('statuscetak')->Length(11)->nullable();
            $table->string('userbukacetak',50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->Length(11)->nullable();            
            $table->longText('info')->nullable();
            $table->integer('statuskirimberkas')->Length(11)->nullable();
            $table->string('userkirimberkas',50)->nullable();
            $table->date('tglkirimberkas')->nullable();
            $table->string('modifiedby',50)->nullable();
            $table->string('editing_by',50)->nullable();            
            $table->dateTime('editing_at')->nullable();            

            $table->timestamps();

            $table->foreign('bank_id', 'pengembaliankasgantungheader_bank_bank_id_foreign')->references('id')->on('bank');    
            $table->foreign('penerimaan_nobukti', 'pengembaliankasgantungheader_penerimaanheader_penerimaan_nobukti_foreign')->references('nobukti')->on('penerimaanheader');    
            $table->foreign('coakasmasuk', 'pengembaliankasgantungheader_akunpusat_coakasmasuk_foreign')->references('coa')->on('akunpusat');    

        });

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
