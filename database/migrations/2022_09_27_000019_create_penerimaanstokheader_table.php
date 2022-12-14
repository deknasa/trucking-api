<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePenerimaanstokheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('penerimaanstokheader');

        Schema::create('penerimaanstokheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->date('tglbukti')->default('1900/1/1');            
            $table->unsignedBigInteger('penerimaanstok_id')->default(0);
            $table->string('penerimaanstok_nobukti',50)->default('');
            $table->string('pengeluaranstok_nobukti',50)->default('');
            $table->string('nobuktisaldo',50)->default('');
            $table->date('tglbuktisaldo')->default('1900/1/1');            
            $table->unsignedBigInteger('supplier_id')->default(0);            
            $table->string('nobon', 50)->default('');
            $table->string('hutang_nobukti', 50)->default('');
            $table->unsignedBigInteger('trado_id')->default('0');
            $table->unsignedBigInteger('gandengan_id')->default('0');
            $table->unsignedBigInteger('gudang_id')->default('0');
            $table->unsignedBigInteger('gudangdari_id')->default('0');
            $table->unsignedBigInteger('gudangke_id')->default('0');            
            $table->string('coa',50)->default('');
            $table->longText('keterangan')->default('');
            $table->unsignedBigInteger('statusformat')->default(0);   
            $table->integer('statuscetak')->Length(11)->default('0');
            $table->string('userbukacetak',50)->default('');
            $table->date('tglbukacetak')->default('1900/1/1');
            $table->integer('jumlahcetak')->Length(11)->default('0');                
            $table->string('modifiedby',50)->default('');
            $table->timestamps();

            $table->foreign('penerimaanstok_id', 'penerimaanstokheader_penerimaanstok_penerimaanstok_id_foreign')->references('id')->on('penerimaanstok');  
            $table->foreign('supplier_id', 'penerimaanstokheader_supplier_supplier_id_foreign')->references('id')->on('supplier');  
            $table->foreign('trado_id', 'penerimaanstokheader_trado_trado_id_foreign')->references('id')->on('trado');  
            $table->foreign('gandengan_id', 'penerimaanstokheader_gandengan_gandengan_id_foreign')->references('id')->on('gandengan');  
            $table->foreign('gudang_id', 'penerimaanstokheader_gudang_gudang_id_foreign')->references('id')->on('gudang');  
            $table->foreign('gudangdari_id', 'penerimaanstokheader_gudang_gudangdari_id_foreign')->references('id')->on('gudang');  
            $table->foreign('gudangke_id', 'penerimaanstokheader_gudang_gudangke_id_foreign')->references('id')->on('gudang');  
            $table->foreign('coa', 'penerimaanstokheader_akunpusat_coa_foreign')->references('coa')->on('akunpusat');  


        });

        DB::statement("ALTER TABLE penerimaanstokheader NOCHECK CONSTRAINT penerimaanstokheader_penerimaanstok_penerimaanstok_id_foreign");
        DB::statement("ALTER TABLE penerimaanstokheader NOCHECK CONSTRAINT penerimaanstokheader_supplier_supplier_id_foreign");
        DB::statement("ALTER TABLE penerimaanstokheader NOCHECK CONSTRAINT penerimaanstokheader_trado_trado_id_foreign");
        DB::statement("ALTER TABLE penerimaanstokheader NOCHECK CONSTRAINT penerimaanstokheader_gandengan_gandengan_id_foreign");
        DB::statement("ALTER TABLE penerimaanstokheader NOCHECK CONSTRAINT penerimaanstokheader_gudang_gudang_id_foreign");
        DB::statement("ALTER TABLE penerimaanstokheader NOCHECK CONSTRAINT penerimaanstokheader_gudang_gudangdari_id_foreign");
        DB::statement("ALTER TABLE penerimaanstokheader NOCHECK CONSTRAINT penerimaanstokheader_gudang_gudangke_id_foreign");
        DB::statement("ALTER TABLE penerimaanstokheader NOCHECK CONSTRAINT penerimaanstokheader_akunpusat_coa_foreign");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('penerimaanstokheader');
    }
}
