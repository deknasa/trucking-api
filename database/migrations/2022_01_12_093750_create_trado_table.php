<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTradoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trado', function (Blueprint $table) {
            $table->id();
            $table->longText('keterangan')->default('');
            $table->integer('statusaktif')->length(11)->default(0);
            $table->double('kmawal', 15,2)->default(0);
            $table->double('kmakhirgantioli', 15,2)->default(0);
            $table->date('tglakhirgantioli')->default('1900/1/1');
            $table->date('tglstnkmati')->default('1900/1/1');
            $table->date('tglasuransimati')->default('1900/1/1');
            $table->string('tahun', 40)->default('');
            $table->string('akhirproduksi', 40)->default('');
            $table->string('merek', 40)->default('');
            $table->string('norangka', 40)->default('');
            $table->string('nomesin', 40)->default('');
            $table->string('nama', 40)->default('');
            $table->string('nostnk', 30)->default('');
            $table->string('alamatstnk', 30)->default('');
            $table->string('modifiedby', 30)->default('');
            $table->date('tglstandarisasi')->default('1900/1/1');
            $table->date('tglserviceopname')->default('1900/1/1');
            $table->integer('statusstandarisasi')->length(11)->default(0);
            $table->string('keteranganprogressstandarisasi', 100)->default('');
            $table->integer('statusjenisplat')->length(11)->default(0);
            $table->date('tglspeksimati')->default('1900/1/1');
            $table->date('tglpajakstnk')->default('1900/1/1');
            $table->date('tglgantiakiterakhir')->default('1900/1/1');
            $table->integer('statusmutasi')->length(11)->default(0);
            $table->integer('statusvalidasikendaraan')->length(11)->default(0);
            $table->string('tipe', 30)->default('');
            $table->string('jenis', 30)->default('');
            $table->integer('isisilinder')->length(11)->default(0);
            $table->string('warna', 30)->default('');
            $table->string('jenisbahanbakar', 30)->default('');
            $table->integer('jumlahsumbu')->length(11)->default(0);
            $table->integer('jumlahroda')->length(11)->default(0);
            $table->string('model', 50)->default('');
            $table->string('nobpkb', 50)->default('');
            $table->integer('statusmobilstoring')->length(11)->default(0);
            $table->unsignedBigInteger('mandor_id')->default(0);
            $table->integer('jumlahbanserap')->length(11)->default(0);
            $table->integer('statusappeditban')->length(11)->default(0);
            $table->integer('statuslewatvalidasi')->length(11)->default(0);
            $table->string('photostnk', 1500)->default('');
            $table->string('photobpkb', 1500)->default('');
            $table->string('phototrado', 1500)->default('');
            $table->timestamps();

            $table->foreign('mandor_id')->references('id')->on('mandor');
        });
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
