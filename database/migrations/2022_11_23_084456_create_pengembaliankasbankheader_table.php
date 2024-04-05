<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePengembaliankasbankheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::dropIfExists('pengembaliankasbankheader');

        Schema::create('pengembaliankasbankheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->date('tglbukti')->nullable();
            $table->longText('keterangan')->nullable();            
            $table->integer('statusjenistransaksi')->Length(11)->nullable();
            $table->string('pengeluaran_nobukti',50)->nullable();
            $table->string('postingdari',50)->nullable();
            $table->integer('statusapproval')->Length(11)->nullable();
            $table->string('dibayarke',250)->nullable();
            $table->unsignedBigInteger('cabang_id')->nullable();
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->unsignedBigInteger('alatbayar_id')->nullable();
            $table->string('userapproval',50)->nullable();
            $table->date('tglapproval')->nullable();
            $table->string('transferkeac',100)->nullable();
            $table->string('transferkean',100)->nullable();
            $table->string('transferkebank',100)->nullable();
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

            $table->foreign('cabang_id', 'pengembaliankasbankheader_cabang_cabang_id_foreign')->references('id')->on('cabang');
            $table->foreign('bank_id', 'pengembaliankasbankheader_bank_bank_id_foreign')->references('id')->on('bank');            
            $table->foreign('alatbayar_id', 'pengembaliankasbankheader_alatbayar_alatbayar_id_foreign')->references('id')->on('alatbayar');            
            $table->foreign('pengeluaran_nobukti', 'pengembaliankasbankheader_pengeluaranheader_pengeluaran_nobukti_foreign')->references('nobukti')->on('pengeluaranheader');            
        });

        DB::statement("ALTER TABLE pengembaliankasbankheader NOCHECK CONSTRAINT pengembaliankasbankheader_cabang_cabang_id_foreign");
        DB::statement("ALTER TABLE pengembaliankasbankheader NOCHECK CONSTRAINT pengembaliankasbankheader_bank_bank_id_foreign");        
        DB::statement("ALTER TABLE pengembaliankasbankheader NOCHECK CONSTRAINT pengembaliankasbankheader_alatbayar_alatbayar_id_foreign");        
        DB::statement("ALTER TABLE pengembaliankasbankheader NOCHECK CONSTRAINT pengembaliankasbankheader_pengeluaranheader_pengeluaran_nobukti_foreign");        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pengembaliankasbankheader');
    }
}
