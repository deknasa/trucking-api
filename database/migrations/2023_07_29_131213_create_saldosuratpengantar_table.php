<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSaldosuratpengantarTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('saldosuratpengantar', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti', 50)->unique();
            $table->string('jobtrucking', 50)->nullable();
            $table->date('tglbukti')->nullable();
            $table->unsignedBigInteger('pelanggan_id')->nullable();
            $table->longText('keterangan')->nullable();
            $table->bigInteger('nourutorder')->nullable();
            $table->unsignedBigInteger('upah_id')->nullable();
            $table->unsignedBigInteger('dari_id')->nullable();
            $table->unsignedBigInteger('sampai_id')->nullable();
            $table->longText('penyesuaian')->nullable();
            $table->unsignedBigInteger('container_id')->nullable();
            $table->string('nocont', 50)->nullable();
            $table->string('nocont2', 50)->nullable();
            $table->string('noseal', 50)->nullable();
            $table->string('noseal2', 50)->nullable();
            $table->unsignedBigInteger('statuscontainer_id')->nullable();
            $table->unsignedBigInteger('trado_id')->nullable();
            $table->unsignedBigInteger('gandengan_id')->nullable();
            $table->unsignedBigInteger('supir_id')->nullable();
            $table->string('nojob', 50)->nullable();
            $table->string('nojob2', 50)->nullable();
            $table->integer('statuslongtrip')->length(11)->nullable();
            $table->integer('statuslangsir')->length(11)->nullable();
            $table->decimal('omset', 15, 2)->nullable();
            $table->decimal('discount', 15, 2)->nullable();
            $table->decimal('totalomset', 15, 2)->nullable();
            $table->decimal('gajisupir', 15, 2)->nullable();
            $table->decimal('gajikenek', 15, 2)->nullable();
            $table->unsignedBigInteger('agen_id')->nullable();
            $table->unsignedBigInteger('jenisorder_id')->nullable();
            $table->integer('statusperalihan')->length(11)->nullable();
            $table->unsignedBigInteger('tarif_id')->nullable();
            $table->decimal('nominalperalihan', 15, 2)->nullable();
            $table->decimal('persentaseperalihan', 15, 2)->nullable();
            $table->unsignedBigInteger('biayatambahan_id')->nullable();
            $table->string('nosp', 50)->nullable();
            $table->date('tglsp')->nullable();
            $table->integer('statusritasiomset')->length(11)->nullable();
            $table->unsignedBigInteger('cabang_id')->nullable();
            $table->decimal('komisisupir', 15, 2)->nullable();
            $table->decimal('tolsupir', 15, 2)->nullable();
            $table->decimal('jarak', 15, 2)->nullable();
            $table->string('nosptagihlain', 50)->nullable();
            $table->decimal('nilaitagihlain', 15, 2)->nullable();
            $table->string('tujuantagih', 50)->nullable();
            $table->decimal('liter', 15, 2)->nullable();
            $table->decimal('nominalstafle', 15, 2)->nullable();
            $table->integer('statusnotif')->length(11)->nullable();
            $table->integer('statusoneway')->length(11)->nullable();
            $table->integer('statusedittujuan')->length(11)->nullable();
            $table->decimal('upahbongkardepo', 15, 2)->nullable();
            $table->decimal('upahmuatdepo', 15, 2)->nullable();
            $table->decimal('hargatol', 15, 2)->nullable();
            $table->decimal('qtyton', 15, 2)->nullable();
            $table->decimal('totalton', 15, 2)->nullable();
            $table->unsignedBigInteger('mandorsupir_id')->nullable();
            $table->unsignedBigInteger('mandortrado_id')->nullable();
            $table->integer('statustrip')->length(11)->nullable();
            $table->string('notripasal', 50)->nullable();
            $table->date('tgldoor')->nullable();
            $table->integer('statusdisc')->length(11)->nullable();
            $table->unsignedBigInteger('statusupahzona')->nullable();
            $table->unsignedBigInteger('zonadari_id')->nullable();
            $table->unsignedBigInteger('zonasampai_id')->nullable();
            $table->unsignedBigInteger('statusformat')->nullable();
            $table->unsignedBigInteger('statusgudangsama')->nullable();
            $table->unsignedBigInteger('statusbatalmuat')->nullable();
            $table->unsignedBigInteger('statusgandengan')->nullable();
            $table->unsignedBigInteger('gandenganasal_id')->nullable();
            $table->string('gudang', 500)->nullable();
            $table->string('lokasibongkarmuat', 500)->nullable();
            $table->integer('statusapprovaleditsuratpengantar')->Length(11)->nullable();
            $table->string('userapprovaleditsuratpengantar', 50)->nullable();
            $table->date('tglapprovaleditsuratpengantar')->nullable();
            $table->unsignedBigInteger('approvalbukatanggal_id')->nullable();
            $table->integer('statusapprovalbiayatitipanemkl')->Length(11)->nullable();
            $table->string('userapprovalbiayatitipanemkl', 50)->nullable();
            $table->date('tglapprovalbiayatitipanemkl')->nullable();            
            $table->date('tglbatasbiayatitipanemkl')->nullable();            
            $table->string('statusric', 50)->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('saldosuratpengantar');
    }
}
