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
            $table->date('tglbukti',50)->nullable();
            $table->longText('keterangan')->nullable();            
            $table->unsignedBigInteger('pengeluaranstok_id')->nullable();
            $table->unsignedBigInteger('trado_id')->nullable();
            $table->unsignedBigInteger('gudang_id')->nullable();
            $table->unsignedBigInteger('gandengan_id')->nullable();
            $table->unsignedBigInteger('supir_id')->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->string('pengeluaranstok_nobukti',50)->nullable();
            $table->string('penerimaanstok_nobukti',50)->nullable();
            $table->string('penerimaanstokproses_nobukti',50)->nullable();            
            $table->string('pengeluarantrucking_nobukti',50)->nullable();
            $table->string('servicein_nobukti',50)->nullable();
            $table->unsignedBigInteger('kerusakan_id')->nullable();
            $table->integer('statuspotongretur')->Length(11)->nullable();
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->string('penerimaan_nobukti',50)->nullable();
            $table->string('coa',50)->nullable();
            $table->string('postingdari',50)->nullable();
            $table->date('tglkasmasuk')->nullable();
            $table->string('hutangbayar_nobukti',50)->nullable();
            $table->unsignedBigInteger('statusformat')->nullable();  
            $table->integer('statuscetak')->Length(11)->nullable();
            $table->string('userbukacetak',50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->Length(11)->nullable();                
            $table->integer('statusapprovaledit')->Length(11)->nullable();
            $table->string('userapprovaledit',50)->nullable();
            $table->date('tglapprovaledit')->nullable();
            $table->dateTime('tglbatasedit')->nullable();            
            $table->integer('statusapprovaleditketerangan')->Length(11)->nullable();
            $table->string('userapprovaleditketerangan', 50)->nullable();
            $table->date('tglapprovaleditketerangan')->nullable();
            $table->dateTime('tglbataseditketerangan')->nullable();            
            $table->longText('info')->nullable();
            $table->string('modifiedby',50)->nullable();
            $table->string('editing_by',50)->nullable();            
            $table->dateTime('editing_at')->nullable();            
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
            $table->foreign('hutangbayar_nobukti', 'pengeluaranstokheader_pelunasanhutangheader_hutangbayar_nobukti_foreign')->references('nobukti')->on('pelunasanhutangheader');

            $schemaManager = Schema::getConnection()->getDoctrineSchemaManager();
            $indexesFound  = $schemaManager->listTableIndexes('pengeluaranstokheader');            

            if (! array_key_exists('pengeluaranstokheader_nobukti_index', $indexesFound)) {
                $table->index('nobukti', 'pengeluaranstokheader_nobukti_index');
            }   

            if (! array_key_exists('pengeluaranstokheader_pengeluaranstok_id_index', $indexesFound)) {
                $table->index('pengeluaranstok_id', 'pengeluaranstokheader_pengeluaranstok_id_index');
            }         
            if (! array_key_exists('pengeluaranstokheader_trado_id_index', $indexesFound)) {
                $table->index('trado_id', 'pengeluaranstokheader_trado_id_index');
            }  
            if (! array_key_exists('pengeluaranstokheader_gudang_id_index', $indexesFound)) {
                $table->index('gudang_id', 'pengeluaranstokheader_gudang_id_index');
            }  
            if (! array_key_exists('pengeluaranstokheader_gandengan_id_index', $indexesFound)) {
                $table->index('gandengan_id', 'pengeluaranstokheader_gandengan_id_index');
            }  
            if (! array_key_exists('pengeluaranstokheader_supir_id_index', $indexesFound)) {
                $table->index('supir_id', 'pengeluaranstokheader_supir_id_index');
            }  
            if (! array_key_exists('pengeluaranstokheader_supplier_id_index', $indexesFound)) {
                $table->index('supplier_id', 'pengeluaranstokheader_supplier_id_index');
            }  
            if (! array_key_exists('pengeluaranstokheader_pengeluaranstok_nobukti_index', $indexesFound)) {
                $table->index('pengeluaranstok_nobukti', 'pengeluaranstokheader_pengeluaranstok_nobukti_index');
            }  
            if (! array_key_exists('pengeluaranstokheader_penerimaanstok_nobukti_index', $indexesFound)) {
                $table->index('penerimaanstok_nobukti', 'pengeluaranstokheader_penerimaanstok_nobukti_index');
            }  
            if (! array_key_exists('pengeluaranstokheader_servicein_nobukti_index', $indexesFound)) {
                $table->index('servicein_nobukti', 'pengeluaranstokheader_servicein_nobukti_index');
            }  
            if (! array_key_exists('pengeluaranstokheader_kerusakan_id_index', $indexesFound)) {
                $table->index('kerusakan_id', 'pengeluaranstokheader_kerusakan_id_index');
            }  
            if (! array_key_exists('pengeluaranstokheader_bank_id_index', $indexesFound)) {
                $table->index('bank_id', 'pengeluaranstokheader_bank_id_index');
            } 
            if (! array_key_exists('pengeluaranstokheader_penerimaan_nobukti_index', $indexesFound)) {
                $table->index('penerimaan_nobukti', 'pengeluaranstokheader_penerimaan_nobukti_index');
            } 
            if (! array_key_exists('pengeluaranstokheader_coa_index', $indexesFound)) {
                $table->index('coa', 'pengeluaranstokheader_coa_index');
            } 
            if (! array_key_exists('pengeluaranstokheader_hutangbayar_nobukti_index', $indexesFound)) {
                $table->index('hutangbayar_nobukti', 'pengeluaranstokheader_hutangbayar_nobukti_index');
            } 
            if (! array_key_exists('pengeluaranstokheader_statusformat_index', $indexesFound)) {
                $table->index('statusformat', 'pengeluaranstokheader_statusformat_index');
            }                                                                                                                                                                                         
            if (! array_key_exists('pengeluaranstokheader_statuscetak_index', $indexesFound)) {
                $table->index('statuscetak', 'pengeluaranstokheader_statuscetak_index');
            }                                                                                                                                                                                         

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
        DB::statement("ALTER TABLE pengeluaranstokheader NOCHECK CONSTRAINT pengeluaranstokheader_pelunasanhutangheader_hutangbayar_nobukti_foreign");
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
