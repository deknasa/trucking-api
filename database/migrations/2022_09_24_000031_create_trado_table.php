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
            $table->string('nostnk', 30)->nullable();
            $table->string('alamatstnk', 30)->nullable();
            $table->string('modifiedby', 30)->nullable();
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
            $table->string('photostnk', 1500)->nullable();
            $table->string('photobpkb', 1500)->nullable();
            $table->string('phototrado', 1500)->nullable();
            $table->timestamps();


            $table->foreign('mandor_id', 'trado_mandor_mandor_id_foreign')->references('id')->on('mandor');
            $table->foreign('supir_id', 'trado_supir_supir_id_foreign')->references('id')->on('supir');
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
