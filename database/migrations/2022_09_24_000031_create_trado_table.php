<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateTradoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('trado');

        Schema::create('trado', function (Blueprint $table) {
            $table->id();
            $table->string('kodetrado', 30)->nullable();
            $table->longText('keterangan')->nullable();
            $table->integer('statusaktif')->length(11)->nullable();
            $table->integer('statusgerobak')->length(11)->nullable();
            $table->double('nominalplusborongan', 15,2)->nullable();
            $table->double('kmawal', 15,2)->nullable();
            $table->double('kmakhirgantioli', 15,2)->nullable();
            $table->date('tglakhirgantioli')->nullable();
            $table->date('tglstnkmati')->nullable();
            $table->date('tglasuransimati')->nullable();
            $table->string('tahun', 40)->nullable();
            $table->string('akhirproduksi', 40)->nullable();
            $table->string('merek', 40)->nullable();
            $table->string('norangka', 40)->nullable();
            $table->string('nomesin', 40)->nullable();
            $table->string('nama', 40)->nullable();
            $table->string('nostnk', 50)->nullable();
            $table->longText('alamatstnk')->nullable();
            $table->date('tglstandarisasi')->nullable();
            $table->date('tglserviceopname')->nullable();
            $table->integer('statusstandarisasi')->length(11)->nullable();
            $table->string('keteranganprogressstandarisasi', 100)->nullable();
            $table->integer('statusjenisplat')->length(11)->nullable();
            $table->date('tglspeksimati')->nullable();
            $table->date('tglpajakstnk')->nullable();
            $table->date('tglgantiakiterakhir')->nullable();
            $table->integer('statusmutasi')->length(11)->nullable();
            $table->integer('statusvalidasikendaraan')->length(11)->nullable();
            $table->string('tipe', 30)->nullable();
            $table->string('jenis', 30)->nullable();
            $table->integer('isisilinder')->length(11)->nullable();
            $table->string('warna', 30)->nullable();
            $table->string('jenisbahanbakar', 30)->nullable();
            $table->integer('jumlahsumbu')->length(11)->nullable();
            $table->integer('jumlahroda')->length(11)->nullable();
            $table->string('model', 50)->nullable();
            $table->string('nobpkb', 50)->nullable();
            $table->integer('statusmobilstoring')->length(11)->nullable();
            $table->unsignedBigInteger('mandor_id')->nullable();
            $table->unsignedBigInteger('supir_id')->nullable();
            $table->integer('jumlahbanserap')->length(11)->nullable();
            $table->integer('statusappeditban')->length(11)->nullable();
            $table->integer('statuslewatvalidasi')->length(11)->nullable();
            $table->integer('statusabsensisupir')->length(11)->nullable();
            $table->string('photostnk', 1500)->nullable();
            $table->string('photobpkb', 1500)->nullable();
            $table->string('phototrado', 1500)->nullable();
            $table->integer('statusapprovalreminderolimesin')->nullable();
            $table->string('userapprovalreminderolimesin', 50)->nullable();
            $table->date('tglapprovalreminderolimesin')->nullable();
            $table->dateTime('tglbatasreminderolimesin')->nullable();           
            $table->integer('statusapprovalreminderolipersneling')->nullable();
            $table->string('userapprovalreminderolipersneling', 50)->nullable();
            $table->date('tglapprovalreminderolipersneling')->nullable();
            $table->dateTime('tglbatasreminderolipersneling')->nullable();            
            $table->integer('statusapprovalreminderoligardan')->nullable();
            $table->string('userapprovalreminderoligardan', 50)->nullable();
            $table->date('tglapprovalreminderoligardan')->nullable();
            $table->dateTime('tglbatasreminderoligardan')->nullable();            
            $table->integer('statusapprovalremindersaringanhawa')->nullable();
            $table->string('userapprovalremindersaringanhawa', 50)->nullable();
            $table->date('tglapprovalremindersaringanhawa')->nullable();
            $table->dateTime('tglbatasremindersaringanhawa')->nullable();            
            $table->dateTime('tglberlakumilikmandor')->nullable();            
            $table->dateTime('tglberlakumiliksupir')->nullable();            
            $table->unsignedBigInteger('tas_id')->nullable();
            $table->string('editing_by',50)->nullable();            
            $table->dateTime('editing_at')->nullable();              
            $table->longText('info')->nullable();
            $table->string('modifiedby', 30)->nullable();
            $table->integer('statusapprovalhistorytradomilikmandor')->nullable();
            $table->string('userapprovalhistorytradomilikmandor', 50)->nullable();
            $table->datetime('tglapprovalhistorytradomilikmandor')->nullable();
            $table->datetime('tglupdatehistorytradomilikmandor')->nullable();
            $table->integer('statusapprovalhistorytradomiliksupir')->nullable();
            $table->string('userapprovalhistorytradomiliksupir', 50)->nullable();
            $table->datetime('tglapprovalhistorytradomiliksupir')->nullable();
            $table->datetime('tglupdatehistorytradomiliksupir')->nullable();

            $table->timestamps();


            $table->foreign('mandor_id', 'trado_mandor_mandor_id_foreign')->references('id')->on('mandor');
            $table->foreign('supir_id', 'trado_supir_supir_id_foreign')->references('id')->on('supir');

            $schemaManager = Schema::getConnection()->getDoctrineSchemaManager();
            $indexesFound  = $schemaManager->listTableIndexes('trado');            

            if (! array_key_exists('trado_kodetrado_index', $indexesFound)) {
                $table->index('kodetrado', 'trado_kodetrado_index');
            }        
            if (! array_key_exists('trado_statusaktif_index', $indexesFound)) {
                $table->index('statusaktif', 'trado_statusaktif_index');
            }        
            if (! array_key_exists('trado_statusgerobak_index', $indexesFound)) {
                $table->index('statusgerobak', 'trado_statusgerobak_index');
            }        
            if (! array_key_exists('trado_statusstandarisasi_index', $indexesFound)) {
                $table->index('statusstandarisasi', 'trado_statusstandarisasi_index');
            }                                            
            if (! array_key_exists('trado_statusjenisplat_index', $indexesFound)) {
                $table->index('statusjenisplat', 'trado_statusjenisplat_index');
            }                                            
            if (! array_key_exists('trado_statusmutasi_index', $indexesFound)) {
                $table->index('statusmutasi', 'trado_statusmutasi_index');
            }                                            
            if (! array_key_exists('trado_statusvalidasikendaraan_index', $indexesFound)) {
                $table->index('statusvalidasikendaraan', 'trado_statusvalidasikendaraan_index');
            }                                            
            if (! array_key_exists('trado_statusmobilstoring_index', $indexesFound)) {
                $table->index('statusmobilstoring', 'trado_statusmobilstoring_index');
            }                                            
            if (! array_key_exists('trado_mandor_id_index', $indexesFound)) {
                $table->index('mandor_id', 'trado_mandor_id_index');
            }                                            
            if (! array_key_exists('trado_supir_id_index', $indexesFound)) {
                $table->index('supir_id', 'trado_supir_id_index');
            }                                            
            if (! array_key_exists('trado_statusappeditban_index', $indexesFound)) {
                $table->index('statusappeditban', 'trado_statusappeditban_index');
            }                                            
            if (! array_key_exists('trado_statuslewatvalidasi_index', $indexesFound)) {
                $table->index('statuslewatvalidasi', 'trado_statuslewatvalidasi_index');
            }                                            
            
        });

        DB::statement("ALTER TABLE trado NOCHECK CONSTRAINT trado_mandor_mandor_id_foreign");
        DB::statement("ALTER TABLE trado NOCHECK CONSTRAINT trado_supir_supir_id_foreign");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trado');
    }
}
