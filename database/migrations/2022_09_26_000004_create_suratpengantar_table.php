<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


class CreateSuratpengantarTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::dropIfExists('suratpengantar');

        Schema::create('suratpengantar', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti', 50)->unique();
            $table->string('jobtrucking', 50)->nullable();
            $table->date('tglbukti')->nullable();
            $table->unsignedBigInteger('pelanggan_id')->nullable();
            $table->longText('keterangan')->nullable();
            $table->bigInteger('nourutorder')->nullable();
            $table->unsignedBigInteger('upah_id')->nullable();
            $table->unsignedBigInteger('upahtangki_id')->nullable();
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
            $table->integer('statuslongtripfull')->length(11)->nullable();
            $table->integer('statuslangsir')->length(11)->nullable();
            $table->decimal('omset', 15, 2)->nullable();
            $table->decimal('discount', 15, 2)->nullable();
            $table->decimal('totalomset', 15, 2)->nullable();
            $table->decimal('gajisupir', 15, 2)->nullable();
            $table->decimal('gajikenek', 15, 2)->nullable();
            $table->unsignedBigInteger('agen_id')->nullable();
            $table->unsignedBigInteger('jenisorder_id')->nullable();
            $table->unsignedBigInteger('jenisorderemkl_id')->nullable();
            $table->integer('statusperalihan')->length(11)->nullable();
            $table->unsignedBigInteger('tarif_id')->nullable();
            $table->unsignedBigInteger('tariftangki_id')->nullable();
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
            $table->unsignedBigInteger('statuskandang')->nullable();
            $table->unsignedBigInteger('statusbatalmuat')->nullable();
            $table->unsignedBigInteger('statusgandengan')->nullable();
            $table->unsignedBigInteger('gandenganasal_id')->nullable();
            $table->string('gudang', 500)->nullable();
            $table->string('lokasibongkarmuat', 500)->nullable();
            $table->string('nobukti_tripasal', 50)->nullable();
            $table->integer('statusapprovaleditsuratpengantar')->Length(11)->nullable();
            $table->string('userapprovaleditsuratpengantar', 50)->nullable();
            $table->date('tglapprovaleditsuratpengantar')->nullable();
            $table->dateTime('tglbataseditsuratpengantar')->nullable();
            $table->unsignedBigInteger('approvalbukatanggal_id')->nullable();
            $table->integer('statusapprovalbiayatitipanemkl')->Length(11)->nullable();
            $table->string('userapprovalbiayatitipanemkl', 50)->nullable();
            $table->date('tglapprovalbiayatitipanemkl')->nullable();
            $table->date('tglbatasbiayatitipanemkl')->nullable();
            $table->integer('statusjeniskendaraan')->Length(11)->nullable();            
            $table->longText('info')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->string('editing_by',50)->nullable();            
            $table->dateTime('editing_at')->nullable();            

            $table->timestamps();

            $table->foreign('jobtrucking', 'suratpengantar_orderantrucking_jobtrucking_foreign')->references('nobukti')->on('orderantrucking');
            $table->foreign('pelanggan_id', 'suratpengantar_pelanggan_pelanggan_id_foreign')->references('id')->on('pelanggan');
            $table->foreign('upah_id', 'suratpengantar_upahsupir_upah_id_foreign')->references('id')->on('upahsupir');
            $table->foreign('dari_id', 'suratpengantar_kota_dari_id_foreign')->references('id')->on('kota');
            $table->foreign('sampai_id', 'suratpengantar_kota_sampai_id_foreign')->references('id')->on('kota');
            $table->foreign('container_id', 'suratpengantar_container_container_id_foreign')->references('id')->on('container');
            $table->foreign('statuscontainer_id', 'suratpengantar_statuscontainer_statuscontainer_id_foreign')->references('id')->on('statuscontainer');
            $table->foreign('trado_id', 'suratpengantar_trado_trado_id_foreign')->references('id')->on('trado');
            $table->foreign('gandengan_id', 'suratpengantar_gandengan_gandengan_id_foreign')->references('id')->on('gandengan');
            $table->foreign('supir_id', 'suratpengantar_supir_supir_id_foreign')->references('id')->on('supir');
            $table->foreign('agen_id', 'suratpengantar_agen_agen_id_foreign')->references('id')->on('agen');
            $table->foreign('jenisorder_id', 'suratpengantar_jenisorder_jenisorder_id_foreign')->references('id')->on('jenisorder');
            $table->foreign('tarif_id', 'suratpengantar_tarif_tarif_id_foreign')->references('id')->on('tarif');
            $table->foreign('cabang_id', 'suratpengantar_cabang_cabang_id_foreign')->references('id')->on('cabang');
            $table->foreign('mandorsupir_id', 'suratpengantar_mandor_mandorsupir_id_foreign')->references('id')->on('mandor');
            $table->foreign('mandortrado_id', 'suratpengantar_mandor_mandortrado_id_foreign')->references('id')->on('mandor');
        });

        DB::statement("ALTER TABLE suratpengantar NOCHECK CONSTRAINT suratpengantar_orderantrucking_jobtrucking_foreign");
        DB::statement("ALTER TABLE suratpengantar NOCHECK CONSTRAINT suratpengantar_pelanggan_pelanggan_id_foreign");
        DB::statement("ALTER TABLE suratpengantar NOCHECK CONSTRAINT suratpengantar_upahsupir_upah_id_foreign");
        DB::statement("ALTER TABLE suratpengantar NOCHECK CONSTRAINT suratpengantar_kota_dari_id_foreign");
        DB::statement("ALTER TABLE suratpengantar NOCHECK CONSTRAINT suratpengantar_kota_sampai_id_foreign");
        DB::statement("ALTER TABLE suratpengantar NOCHECK CONSTRAINT suratpengantar_container_container_id_foreign");
        DB::statement("ALTER TABLE suratpengantar NOCHECK CONSTRAINT suratpengantar_statuscontainer_statuscontainer_id_foreign");
        DB::statement("ALTER TABLE suratpengantar NOCHECK CONSTRAINT suratpengantar_trado_trado_id_foreign");
        DB::statement("ALTER TABLE suratpengantar NOCHECK CONSTRAINT suratpengantar_gandengan_gandengan_id_foreign");
        DB::statement("ALTER TABLE suratpengantar NOCHECK CONSTRAINT suratpengantar_supir_supir_id_foreign");
        DB::statement("ALTER TABLE suratpengantar NOCHECK CONSTRAINT suratpengantar_agen_agen_id_foreign");
        DB::statement("ALTER TABLE suratpengantar NOCHECK CONSTRAINT suratpengantar_jenisorder_jenisorder_id_foreign");
        DB::statement("ALTER TABLE suratpengantar NOCHECK CONSTRAINT suratpengantar_tarif_tarif_id_foreign");
        DB::statement("ALTER TABLE suratpengantar NOCHECK CONSTRAINT suratpengantar_cabang_cabang_id_foreign");
        DB::statement("ALTER TABLE suratpengantar NOCHECK CONSTRAINT suratpengantar_mandor_mandorsupir_id_foreign");
        DB::statement("ALTER TABLE suratpengantar NOCHECK CONSTRAINT suratpengantar_mandor_mandortrado_id_foreign");
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
