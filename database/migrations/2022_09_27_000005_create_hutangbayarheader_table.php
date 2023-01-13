<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateHutangbayarheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('hutangbayarheader');

        Schema::create('hutangbayarheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti', 50)->unique();            
            $table->date('tglbukti')->default('1900/1/1');            
            $table->unsignedBigInteger('bank_id')->default('0');            
            $table->unsignedBigInteger('supplier_id')->default('0');            
            $table->unsignedBigInteger('pelanggan_id')->default('0');            
            $table->string('pengeluaran_nobukti', 50)->default('');            
            $table->string('coa',50)->default('');            
            $table->unsignedBigInteger('alatbayar_id')->default(0);
            $table->date('tglcair')->default('1900/1/1');
            $table->string('nowarkat',100)->default('');            
            $table->integer('statusapproval')->length(11)->default('0');            
            $table->date('tglapproval')->default('1900/1/1');            
            $table->string('userapproval', 50)->default('');
            $table->unsignedBigInteger('statusformat')->default(0);             
            $table->integer('statuscetak')->Length(11)->default('0');
            $table->string('userbukacetak',50)->default('');
            $table->date('tglbukacetak')->default('1900/1/1');
            $table->integer('jumlahcetak')->Length(11)->default('0');
            $table->string('modifiedby', 50)->default('');            
            $table->timestamps();


            $table->foreign('supplier_id', 'hutangbayarheader_supplier_supplier_id_foreign')->references('id')->on('supplier');    
            $table->foreign('pelanggan_id', 'hutangbayarheader_pelanggan_pelanggan_id_foreign')->references('id')->on('pelanggan');    
            $table->foreign('bank_id', 'hutangbayarheader_bank_bank_id_foreign')->references('id')->on('bank');    
            $table->foreign('coa', 'hutangbayarheader_akunpusat_coa_foreign')->references('coa')->on('akunpusat');    
            $table->foreign('pengeluaran_nobukti', 'hutangbayarheader_pengeluaranheader_pengeluaran_nobukti_foreign')->references('nobukti')->on('pengeluaranheader');    
            $table->foreign('alatbayar_id', 'hutangbayarheader_alatbayar_alatbayar_id_foreign')->references('id')->on('alatbayar');    


        });
        DB::statement("ALTER TABLE hutangbayarheader NOCHECK CONSTRAINT hutangbayarheader_supplier_supplier_id_foreign");
        DB::statement("ALTER TABLE hutangbayarheader NOCHECK CONSTRAINT hutangbayarheader_pelanggan_pelanggan_id_foreign");
        DB::statement("ALTER TABLE hutangbayarheader NOCHECK CONSTRAINT hutangbayarheader_bank_bank_id_foreign");
        DB::statement("ALTER TABLE hutangbayarheader NOCHECK CONSTRAINT hutangbayarheader_akunpusat_coa_foreign");
        DB::statement("ALTER TABLE hutangbayarheader NOCHECK CONSTRAINT hutangbayarheader_pengeluaranheader_pengeluaran_nobukti_foreign");
        DB::statement("ALTER TABLE hutangbayarheader NOCHECK CONSTRAINT hutangbayarheader_alatbayar_alatbayar_id_foreign");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hutangbayarheader');
    }
}
