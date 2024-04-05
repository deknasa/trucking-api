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
            $table->string('nobukti', 50)->unique();
            $table->date('tglbukti')->nullable();
            $table->unsignedBigInteger('penerimaanstok_id')->nullable();
            $table->string('penerimaanstok_nobukti', 50)->nullable();
            $table->string('pengeluaranstok_nobukti', 50)->nullable();
            $table->string('pengeluaranstokproses_nobukti',50)->nullable();
            $table->string('nobuktisaldo', 50)->nullable();
            $table->date('tglbuktisaldo')->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->string('nobon', 50)->nullable();
            $table->string('hutang_nobukti', 50)->nullable();
            $table->unsignedBigInteger('trado_id')->nullable();
            $table->unsignedBigInteger('gandengan_id')->nullable();
            $table->unsignedBigInteger('gudang_id')->nullable();
            $table->integer('statuspindahgudang')->Length(11)->nullable();
            $table->unsignedBigInteger('gudangdari_id')->nullable();
            $table->unsignedBigInteger('gudangke_id')->nullable();
            $table->unsignedBigInteger('tradodari_id')->nullable();
            $table->unsignedBigInteger('tradoke_id')->nullable();
            $table->unsignedBigInteger('gandengandari_id')->nullable();
            $table->unsignedBigInteger('gandenganke_id')->nullable();
            $table->string('coa', 50)->nullable();
            $table->longText('keterangan')->nullable();
            $table->unsignedBigInteger('statusformat')->nullable();
            $table->integer('statuscetak')->Length(11)->nullable();
            $table->string('userbukacetak', 50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->Length(11)->nullable();
            $table->integer('statusapprovaledit')->Length(11)->nullable();
            $table->string('userapprovaledit', 50)->nullable();
            $table->date('tglapprovaledit')->nullable();
            $table->dateTime('tglbatasedit')->nullable();
            $table->integer('statusapprovaleditketerangan')->Length(11)->nullable();
            $table->string('userapprovaleditketerangan', 50)->nullable();
            $table->date('tglapprovaleditketerangan')->nullable();
            $table->dateTime('tglbataseditketerangan')->nullable();
            $table->integer('statuskirimberkas')->Length(11)->nullable();
            $table->string('userkirimberkas',50)->nullable();
            $table->date('tglkirimberkas')->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->string('editing_by',50)->nullable();            
            $table->dateTime('editing_at')->nullable();            
            $table->timestamps();

            $table->foreign('penerimaanstok_id', 'penerimaanstokheader_penerimaanstok_penerimaanstok_id_foreign')->references('id')->on('penerimaanstok');
            $table->foreign('supplier_id', 'penerimaanstokheader_supplier_supplier_id_foreign')->references('id')->on('supplier');
            $table->foreign('trado_id', 'penerimaanstokheader_trado_trado_id_foreign')->references('id')->on('trado');
            $table->foreign('gandengan_id', 'penerimaanstokheader_gandengan_gandengan_id_foreign')->references('id')->on('gandengan');
            $table->foreign('gudang_id', 'penerimaanstokheader_gudang_gudang_id_foreign')->references('id')->on('gudang');
            $table->foreign('gudangdari_id', 'penerimaanstokheader_gudang_gudangdari_id_foreign')->references('id')->on('gudang');
            $table->foreign('gudangke_id', 'penerimaanstokheader_gudang_gudangke_id_foreign')->references('id')->on('gudang');
            $table->foreign('tradodari_id', 'penerimaanstokheader_trado_tradodari_id_foreign')->references('id')->on('trado');
            $table->foreign('tradoke_id', 'penerimaanstokheader_trado_tradoke_id_foreign')->references('id')->on('trado');
            $table->foreign('gandengandari_id', 'penerimaanstokheader_gandengan_gandengandari_id_foreign')->references('id')->on('gandengan');
            $table->foreign('gandenganke_id', 'penerimaanstokheader_gandengan_gandenganke_id_foreign')->references('id')->on('gandengan');
            $table->foreign('coa', 'penerimaanstokheader_akunpusat_coa_foreign')->references('coa')->on('akunpusat');

            $schemaManager = Schema::getConnection()->getDoctrineSchemaManager();
            $indexesFound  = $schemaManager->listTableIndexes('penerimaanstokheader');

            if (!array_key_exists('penerimaanstokheader_nobukti_index', $indexesFound)) {
                $table->index('nobukti', 'penerimaanstokheader_nobukti_index');
            }
            if (!array_key_exists('penerimaanstokheader_penerimaanstok_id_index', $indexesFound)) {
                $table->index('penerimaanstok_id', 'penerimaanstokheader_penerimaanstok_id_index');
            }
            if (!array_key_exists('penerimaanstokheader_penerimaanstok_nobukti_index', $indexesFound)) {
                $table->index('penerimaanstok_nobukti', 'penerimaanstokheader_penerimaanstok_nobukti_index');
            }
            if (!array_key_exists('penerimaanstokheader_pengeluaranstok_nobukti_index', $indexesFound)) {
                $table->index('pengeluaranstok_nobukti', 'penerimaanstokheader_pengeluaranstok_nobukti_index');
            }
            if (!array_key_exists('penerimaanstokheader_supplier_id_index', $indexesFound)) {
                $table->index('supplier_id', 'penerimaanstokheader_supplier_id_index');
            }
            if (!array_key_exists('penerimaanstokheader_hutang_nobukti_index', $indexesFound)) {
                $table->index('hutang_nobukti', 'penerimaanstokheader_hutang_nobukti_index');
            }
            if (!array_key_exists('penerimaanstokheader_trado_id_index', $indexesFound)) {
                $table->index('trado_id', 'penerimaanstokheader_trado_id_index');
            }
            if (!array_key_exists('penerimaanstokheader_gandengan_id_index', $indexesFound)) {
                $table->index('gandengan_id', 'penerimaanstokheader_gandengan_id_index');
            }
            if (!array_key_exists('penerimaanstokheader_gudang_id_index', $indexesFound)) {
                $table->index('gudang_id', 'penerimaanstokheader_gudang_id_index');
            }
            if (!array_key_exists('penerimaanstokheader_statuspindahgudang_index', $indexesFound)) {
                $table->index('statuspindahgudang', 'penerimaanstokheader_statuspindahgudang_index');
            }
            if (!array_key_exists('penerimaanstokheader_gudangdari_id_index', $indexesFound)) {
                $table->index('gudangdari_id', 'penerimaanstokheader_gudangdari_id_index');
            }
            if (!array_key_exists('penerimaanstokheader_gudangke_id_index', $indexesFound)) {
                $table->index('gudangke_id', 'penerimaanstokheader_gudangke_id_index');
            }
            if (!array_key_exists('penerimaanstokheader_tradodari_id_index', $indexesFound)) {
                $table->index('tradodari_id', 'penerimaanstokheader_tradodari_id_index');
            }
            if (!array_key_exists('penerimaanstokheader_tradoke_id_index', $indexesFound)) {
                $table->index('tradoke_id', 'penerimaanstokheader_tradoke_id_index');
            }
            if (!array_key_exists('penerimaanstokheader_gandengandari_id_index', $indexesFound)) {
                $table->index('gandengandari_id', 'penerimaanstokheader_gandengandari_id_index');
            }
            if (!array_key_exists('penerimaanstokheader_gandenganke_id_index', $indexesFound)) {
                $table->index('gandenganke_id', 'penerimaanstokheader_gandenganke_id_index');
            }
            if (!array_key_exists('penerimaanstokheader_coa_index', $indexesFound)) {
                $table->index('coa', 'penerimaanstokheader_coa_index');
            }
            if (!array_key_exists('penerimaanstokheader_statusformat_index', $indexesFound)) {
                $table->index('statusformat', 'penerimaanstokheader_statusformat_index');
            }
            if (!array_key_exists('penerimaanstokheader_statuscetak_index', $indexesFound)) {
                $table->index('statuscetak', 'penerimaanstokheader_statuscetak_index');
            }
        });

        DB::statement("ALTER TABLE penerimaanstokheader NOCHECK CONSTRAINT penerimaanstokheader_penerimaanstok_penerimaanstok_id_foreign");
        DB::statement("ALTER TABLE penerimaanstokheader NOCHECK CONSTRAINT penerimaanstokheader_supplier_supplier_id_foreign");
        DB::statement("ALTER TABLE penerimaanstokheader NOCHECK CONSTRAINT penerimaanstokheader_trado_trado_id_foreign");
        DB::statement("ALTER TABLE penerimaanstokheader NOCHECK CONSTRAINT penerimaanstokheader_gandengan_gandengan_id_foreign");
        DB::statement("ALTER TABLE penerimaanstokheader NOCHECK CONSTRAINT penerimaanstokheader_gudang_gudang_id_foreign");
        DB::statement("ALTER TABLE penerimaanstokheader NOCHECK CONSTRAINT penerimaanstokheader_gudang_gudangdari_id_foreign");
        DB::statement("ALTER TABLE penerimaanstokheader NOCHECK CONSTRAINT penerimaanstokheader_gudang_gudangke_id_foreign");
        DB::statement("ALTER TABLE penerimaanstokheader NOCHECK CONSTRAINT penerimaanstokheader_trado_tradodari_id_foreign");
        DB::statement("ALTER TABLE penerimaanstokheader NOCHECK CONSTRAINT penerimaanstokheader_trado_tradoke_id_foreign");
        DB::statement("ALTER TABLE penerimaanstokheader NOCHECK CONSTRAINT penerimaanstokheader_gandengan_gandengandari_id_foreign");
        DB::statement("ALTER TABLE penerimaanstokheader NOCHECK CONSTRAINT penerimaanstokheader_gandengan_gandenganke_id_foreign");
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
