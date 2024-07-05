<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePelunasanhutanglamaheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('pelunasanhutanglamaheader');

        Schema::create('pelunasanhutanglamaheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti', 50)->unique();
            $table->date('tglbukti')->nullable();
            $table->longText('keterangan')->nullable();
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->unsignedBigInteger('pelanggan_id')->nullable();
            $table->integer('statusbayarhutang')->Length(11)->nullable();
            $table->string('pengeluaran_nobukti', 50)->nullable();
            $table->string('coa', 50)->nullable();
            $table->unsignedBigInteger('alatbayar_id')->nullable();
            $table->date('tglcair')->nullable();
            $table->string('nowarkat', 100)->nullable();
            $table->integer('statusapproval')->length(11)->nullable();
            $table->date('tglapproval')->nullable();
            $table->string('userapproval', 50)->nullable();
            $table->unsignedBigInteger('statusformat')->nullable();
            $table->integer('statuscetak')->Length(11)->nullable();
            $table->string('userbukacetak', 50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->Length(11)->nullable();
            $table->integer('statuskirimberkas')->Length(11)->nullable();
            $table->string('userkirimberkas',50)->nullable();
            $table->date('tglkirimberkas')->nullable();

            $table->longText('info')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->string('editing_by',50)->nullable();            
            $table->dateTime('editing_at')->nullable();          
            $table->timestamps();

            $table->foreign('supplier_id', 'pelunasanhutanglamaheader_supplier_supplier_id_foreign')->references('id')->on('supplier');    
            $table->foreign('pelanggan_id', 'pelunasanhutanglamaheader_pelanggan_pelanggan_id_foreign')->references('id')->on('pelanggan');    
            $table->foreign('bank_id', 'pelunasanhutanglamaheader_bank_bank_id_foreign')->references('id')->on('bank');    
            $table->foreign('coa', 'pelunasanhutanglamaheader_akunpusat_coa_foreign')->references('coa')->on('akunpusat');    
            $table->foreign('pengeluaran_nobukti', 'pelunasanhutanglamaheader_pengeluaranheader_pengeluaran_nobukti_foreign')->references('nobukti')->on('pengeluaranheader');    
            $table->foreign('alatbayar_id', 'pelunasanhutanglamaheader_alatbayar_alatbayar_id_foreign')->references('id')->on('alatbayar');                
        });
        
        DB::statement("ALTER TABLE pelunasanhutanglamaheader NOCHECK CONSTRAINT pelunasanhutanglamaheader_supplier_supplier_id_foreign");
        DB::statement("ALTER TABLE pelunasanhutanglamaheader NOCHECK CONSTRAINT pelunasanhutanglamaheader_pelanggan_pelanggan_id_foreign");
        DB::statement("ALTER TABLE pelunasanhutanglamaheader NOCHECK CONSTRAINT pelunasanhutanglamaheader_bank_bank_id_foreign");
        DB::statement("ALTER TABLE pelunasanhutanglamaheader NOCHECK CONSTRAINT pelunasanhutanglamaheader_akunpusat_coa_foreign");
        DB::statement("ALTER TABLE pelunasanhutanglamaheader NOCHECK CONSTRAINT pelunasanhutanglamaheader_pengeluaranheader_pengeluaran_nobukti_foreign");
        DB::statement("ALTER TABLE pelunasanhutanglamaheader NOCHECK CONSTRAINT pelunasanhutanglamaheader_alatbayar_alatbayar_id_foreign");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pelunasanhutanglamaheader');
    }
}