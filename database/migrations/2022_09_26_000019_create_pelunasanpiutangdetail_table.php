<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePelunasanpiutangdetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::dropIfExists('pelunasanpiutangdetail');

        Schema::create('pelunasanpiutangdetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pelunasanpiutang_id')->default('0');
            $table->string('nobukti',50)->default('');
            $table->unsignedBigInteger('pelanggan_id')->default('0');
            $table->unsignedBigInteger('agen_id')->default('0');
            $table->double('nominal',15,2)->default('0');
            $table->string('piutang_nobukti',50)->default('');
            $table->integer('cicilan')->length(11)->default('0');
            $table->date('tglcair')->default('1900/1/1');
            $table->longText('keterangan')->default('');
            $table->date('tgljt')->default('1900/1/1');
            $table->double('penyesuaian',15,2)->default('0');
            $table->string('coapenyesuaian',50)->default('');
            $table->double('nominallebihbayar',15,2)->default('0');
            $table->string('coalebihbayar',50)->default('');
            $table->string('invoice_nobukti',50)->default('');
            $table->longText('keteranganpenyesuaian')->default('');
            $table->string('modifiedby',50)->default('');
            $table->timestamps();

             $table->foreign('pelunasanpiutang_id', 'pelunasanpiutangdetail_pelunasanpiutangheader_pelunasanpiutang_id_foreign')->references('id')->on('pelunasanpiutangheader')->onDelete('cascade');  
            $table->foreign('pelanggan_id', 'pelunasanpiutangdetail_pelanggan_pelanggan_id_foreign')->references('id')->on('pelanggan');
            $table->foreign('agen_id', 'pelunasanpiutangdetail_agen_agen_id_foreign')->references('id')->on('agen');
            $table->foreign('piutang_nobukti', 'pelunasanpiutangdetail_piutangheader_piutang_nobukti_foreign')->references('nobukti')->on('piutangheader');
            $table->foreign('invoice_nobukti', 'pelunasanpiutangdetail_invoiceheader_invoice_nobukti_foreign')->references('nobukti')->on('invoiceheader');
            $table->foreign('coapenyesuaian', 'pelunasanpiutangdetail_akunpusat_coapenyesuaian_foreign')->references('coa')->on('akunpusat');
            $table->foreign('coalebihbayar', 'pelunasanpiutangdetail_akunpusat_coalebihbayar_foreign')->references('coa')->on('akunpusat');

        });

        
        DB::statement("ALTER TABLE pelunasanpiutangdetail NOCHECK CONSTRAINT pelunasanpiutangdetail_pelanggan_pelanggan_id_foreign");
        DB::statement("ALTER TABLE pelunasanpiutangdetail NOCHECK CONSTRAINT pelunasanpiutangdetail_agen_agen_id_foreign");
        DB::statement("ALTER TABLE pelunasanpiutangdetail NOCHECK CONSTRAINT pelunasanpiutangdetail_piutangheader_piutang_nobukti_foreign");
        DB::statement("ALTER TABLE pelunasanpiutangdetail NOCHECK CONSTRAINT pelunasanpiutangdetail_invoiceheader_invoice_nobukti_foreign");
        DB::statement("ALTER TABLE pelunasanpiutangdetail NOCHECK CONSTRAINT pelunasanpiutangdetail_akunpusat_coapenyesuaian_foreign");
        DB::statement("ALTER TABLE pelunasanpiutangdetail NOCHECK CONSTRAINT pelunasanpiutangdetail_akunpusat_coalebihbayar_foreign");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pelunasanpiutangdetail');
    }
}
