<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateSuratpengantarTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('suratpengantar', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->date('tglbukti')->default('1900/1/1');
            $table->unsignedBigInteger('pelanggan_id')->default('0');
            $table->longText('keterangan')->default('');
            $table->bigInteger('nourutorder')->default('0');
            $table->unsignedBigInteger('upah_id')->default('0');
            $table->unsignedBigInteger('dari_id')->default('0');
            $table->unsignedBigInteger('sampai_id')->default('0');
            $table->unsignedBigInteger('container_id')->default('0');
            $table->string('nocont',50)->default('');
            $table->string('nocont2',50)->default('');
            $table->unsignedBigInteger('statuscontainer_id')->default('0');
            $table->unsignedBigInteger('trado_id')->default('0');
            $table->unsignedBigInteger('supir_id')->default('0');
            $table->string('nojob',50)->default('');
            $table->string('nojob2',50)->default('');
            $table->longText('keteranganritasi')->default('');
            $table->unsignedBigInteger('ritasidari_id')->default('0');
            $table->unsignedBigInteger('ritasisampai_id')->default('0');
            $table->integer('statuslongtrip')->length(11)->default('0');
            $table->decimal('gajisupir',15,2)->default('0');
            $table->decimal('gajikenek',15,2)->default('0');
            $table->decimal('gajiritasi',15,2)->default('0');
            $table->unsignedBigInteger('agen_id')->default('0');
            $table->unsignedBigInteger('jenisorder_id')->default('0');
            $table->integer('statusperalihan')->length(11)->default('0');
            $table->unsignedBigInteger('tarif_id')->default('0');
            $table->decimal('nominalperalihan',15,2)->default('0');
            $table->decimal('persentaseperalihan',15,2)->default('0');
            $table->unsignedBigInteger('biayatambahan_id')->default('0');
            $table->string('nosp',50)->default('');
            $table->date('tglsp')->default('1900/1/1');
            $table->integer('statusritasiomset')->length(11)->default('0');
            $table->unsignedBigInteger('cabang_id')->default('0');
            $table->decimal('komisisupir',15,2)->default('0');
            $table->decimal('tolsupir',15,2)->default('0');
            $table->decimal('jarak',15,2)->default('0');
            $table->string('nosptagihlain',50)->default('');
            $table->decimal('nilaitagihlain',15,2)->default('0');
            $table->string('tujuantagih',50)->default('');
            $table->decimal('liter',15,2)->default('0');
            $table->decimal('nominalstafle',15,2)->default('0');
            $table->integer('statusnotif')->length(11)->default('0');
            $table->integer('statusoneway')->length(11)->default('0');
            $table->integer('statusedittujuan')->length(11)->default('0');
            $table->decimal('upahbongkardepo',15,2)->default('0');
            $table->decimal('upahmuatdepo',15,2)->default('0');
            $table->decimal('hargatol',15,2)->default('0');
            $table->decimal('qtyton',15,2)->default('0');
            $table->decimal('totalton',15,2)->default('0');
            $table->unsignedBigInteger('mandorsupir_id')->default('0');
            $table->unsignedBigInteger('mandortrado_id')->default('0');
            $table->integer('statustrip')->length(11)->default('0');
            $table->string('notripasal',50)->default('');
            $table->date('tgldoor')->default('1900/1/1');
            $table->unsignedBigInteger('upahritasi_id')->default('0');
            $table->integer('statusdisc')->length(11)->default('0');
            $table->string('modifiedby',50)->default('');
            $table->timestamps();

            $table->foreign('pelanggan_id')->references('id')->on('pelanggan');
            $table->foreign('upah_id')->references('id')->on('upahsupir');
            $table->foreign('dari_id')->references('id')->on('kota');
            $table->foreign('sampai_id')->references('id')->on('kota');
            $table->foreign('container_id')->references('id')->on('container');
            $table->foreign('statuscontainer_id')->references('id')->on('statuscontainer');
            $table->foreign('trado_id')->references('id')->on('trado');
            $table->foreign('supir_id')->references('id')->on('supir');
            $table->foreign('ritasidari_id')->references('id')->on('kota');
            $table->foreign('ritasisampai_id')->references('id')->on('kota');
            $table->foreign('agen_id')->references('id')->on('agen');
            $table->foreign('jenisorder_id')->references('id')->on('jenisorder');
            $table->foreign('tarif_id')->references('id')->on('tarif');
            $table->foreign('cabang_id')->references('id')->on('cabang');
            $table->foreign('mandorsupir_id')->references('id')->on('mandor');
            $table->foreign('mandortrado_id')->references('id')->on('mandor');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('suratpengantar');
    }
}
