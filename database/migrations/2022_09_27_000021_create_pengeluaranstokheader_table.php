<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePengeluaranstokheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('pengeluaranstokheader');

        Schema::create('pengeluaranstokheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->date('tglbukti',50)->default('1900/1/1');
            $table->unsignedBigInteger('pengeluaranstok_id')->default(0);
            $table->unsignedBigInteger('trado_id')->default('0');
            $table->unsignedBigInteger('gudang_id')->default('0');
            $table->unsignedBigInteger('gandengan_id')->default('0');
            $table->unsignedBigInteger('supir_id')->default('0');
            $table->unsignedBigInteger('supplier_id')->default('0');
            $table->string('pengeluaranstok_nobukti',50)->default('');
            $table->string('penerimaanstok_nobukti',50)->default('');
            $table->string('servicein_nobukti',50)->default('');
            $table->unsignedBigInteger('kerusakan_id')->default('0');
            $table->integer('statuspotongretur')->Length(11)->default('0');
            $table->unsignedBigInteger('bank_id')->default('0');
            $table->string('penerimaan_nobukti',50)->default('');
            $table->string('coa',50)->default('');
            $table->string('postingdari',50)->default('');
            $table->date('tglkasmasuk')->default('1900/1/1');
            $table->string('hutangbayar_nobukti',50)->default('');
            $table->unsignedBigInteger('statusformat')->default(0);  
            $table->integer('statuscetak')->Length(11)->default('0');
            $table->string('userbukacetak',50)->default('');
            $table->date('tglbukacetak')->default('1900/1/1');
            $table->integer('jumlahcetak')->Length(11)->default('0');                
            $table->string('modifiedby',50)->default('');
            $table->timestamps();



            $table->foreign('pengeluaranstok_id', 'pengeluaranstokheader_pengeluaranstok_pengeluaranstok_id_foreign')->references('id')->on('pengeluaranstok');  
            $table->foreign('trado_id', 'pengeluaranstokheader_trado_trado_id_foreign')->references('id')->on('trado');  
            $table->foreign('bank_id', 'pengeluaranstokheader_bank_bank_id_foreign')->references('id')->on('bank');  
            $table->foreign('gandengan_id', 'pengeluaranstokheader_gandengan_gandengan_id_foreign')->references('id')->on('gandengan');  
            $table->foreign('gudang_id', 'pengeluaranstokheader_gudang_gudang_id_foreign')->references('id')->on('gudang');  
            $table->foreign('supir_id', 'pengeluaranstokheader_gudang_supir_id_foreign')->references('id')->on('supir');  
            $table->foreign('supplier_id', 'pengeluaranstokheader_supplier_supplier_id_foreign')->references('id')->on('supplier');  
            $table->foreign('kerusakan_id', 'pengeluaranstokheader_kerusakan_kerusakan_id_foreign')->references('id')->on('kerusakan');  
            $table->foreign('servicein_nobukti', 'pengeluaranstokheader_servicein_servicein_nobukti_foreign')->references('nobukti')->on('serviceinheader');  
            $table->foreign('penerimaan_nobukti', 'pengeluaranstokheader_penerimaanheader_penerimaan_nobukti_foreign')->references('nobukti')->on('penerimaanheader');
            $table->foreign('hutangbayar_nobukti', 'pengeluaranstokheader_hutangbayarheader_hutangbayar_nobukti_foreign')->references('nobukti')->on('hutangbayarheader');

        });

        DB::statement("ALTER TABLE pengeluaranstokheader NOCHECK CONSTRAINT pengeluaranstokheader_bank_bank_id_foreign");
        DB::statement("ALTER TABLE pengeluaranstokheader NOCHECK CONSTRAINT pengeluaranstokheader_pengeluaranstok_pengeluaranstok_id_foreign");
        DB::statement("ALTER TABLE pengeluaranstokheader NOCHECK CONSTRAINT pengeluaranstokheader_trado_trado_id_foreign");
        DB::statement("ALTER TABLE pengeluaranstokheader NOCHECK CONSTRAINT pengeluaranstokheader_gandengan_gandengan_id_foreign");
        DB::statement("ALTER TABLE pengeluaranstokheader NOCHECK CONSTRAINT pengeluaranstokheader_gudang_gudang_id_foreign");
        DB::statement("ALTER TABLE pengeluaranstokheader NOCHECK CONSTRAINT pengeluaranstokheader_gudang_supir_id_foreign");
        DB::statement("ALTER TABLE pengeluaranstokheader NOCHECK CONSTRAINT pengeluaranstokheader_supplier_supplier_id_foreign");
        DB::statement("ALTER TABLE pengeluaranstokheader NOCHECK CONSTRAINT pengeluaranstokheader_kerusakan_kerusakan_id_foreign");
        DB::statement("ALTER TABLE pengeluaranstokheader NOCHECK CONSTRAINT pengeluaranstokheader_servicein_servicein_nobukti_foreign");
        DB::statement("ALTER TABLE pengeluaranstokheader NOCHECK CONSTRAINT pengeluaranstokheader_penerimaanheader_penerimaan_nobukti_foreign");
        DB::statement("ALTER TABLE pengeluaranstokheader NOCHECK CONSTRAINT pengeluaranstokheader_hutangbayarheader_hutangbayar_nobukti_foreign");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pengeluaranstokheader');
    }
}
