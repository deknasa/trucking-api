<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePelunasanpiutangheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('pelunasanpiutangheader');

        Schema::create('pelunasanpiutangheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->date('tglbukti')->default('1900/1/1');
            $table->unsignedBigInteger('bank_id')->default('0');
            $table->unsignedBigInteger('agen_id')->default('0');
            $table->unsignedBigInteger('cabang_id')->default('0');
            $table->unsignedBigInteger('alatbayar_id')->default('0');
            $table->unsignedBigInteger('pelanggan_id')->default('0');
            $table->string('penerimaangiro_nobukti',50)->default('');
            $table->string('penerimaan_nobukti',50)->default('');
            $table->string('notakredit_nobukti',50)->default('');
            $table->string('notadebet_nobukti',50)->default('');
            $table->date('tglcair')->default('1900/1/1');
            $table->string('nowarkat',50)->default('');
            $table->unsignedBigInteger('statusformat')->default(0);
            $table->string('modifiedby',50)->default('');
            $table->timestamps();

            $table->foreign('bank_id', 'pelunasanpiutangheader_bank_bank_id_foreign')->references('id')->on('bank');
            $table->foreign('agen_id', 'pelunasanpiutangheader_agen_agen_id_foreign')->references('id')->on('agen');
            $table->foreign('pelanggan_id', 'pelunasanpiutangheader_pelanggan_pelanggan_id_foreign')->references('id')->on('pelanggan');
            $table->foreign('cabang_id', 'pelunasanpiutangheader_cabang_cabang_id_foreign')->references('id')->on('cabang');
            $table->foreign('alatbayar_id', 'pelunasanpiutangheader_alatbayar_alatbayar_id_foreign')->references('id')->on('alatbayar');
            $table->foreign('penerimaangiro_nobukti', 'pelunasanpiutangheader_penerimaangiroheader_nobukti_foreign')->references('id')->on('penerimaangiroheader');
            $table->foreign('penerimaan_nobukti', 'pelunasanpiutangheader_penerimaanheader_nobukti_foreign')->references('id')->on('penerimaanheader');
            $table->foreign('notakredit_nobukti', 'pelunasanpiutangheader_notakredit_nobukti_foreign')->references('id')->on('notakreditheader');
            $table->foreign('notadebet_nobukti', 'pelunasanpiutangheader_notadebet_nobukti_foreign')->references('id')->on('notadebetheader');


        });

        DB::statement("ALTER TABLE pelunasanpiutangheader NOCHECK CONSTRAINT pelunasanpiutangheader_bank_bank_id_foreign");
        DB::statement("ALTER TABLE pelunasanpiutangheader NOCHECK CONSTRAINT pelunasanpiutangheader_agen_agen_id_foreign");
        DB::statement("ALTER TABLE pelunasanpiutangheader NOCHECK CONSTRAINT pelunasanpiutangheader_pelanggan_pelanggan_id_foreign");
        DB::statement("ALTER TABLE pelunasanpiutangheader NOCHECK CONSTRAINT pelunasanpiutangheader_cabang_cabang_id_foreign");
        DB::statement("ALTER TABLE pelunasanpiutangheader NOCHECK CONSTRAINT pelunasanpiutangheader_penerimaangiroheader_nobukti_foreign");
        DB::statement("ALTER TABLE pelunasanpiutangheader NOCHECK CONSTRAINT pelunasanpiutangheader_penerimaanheader_nobukti_foreign");
        DB::statement("ALTER TABLE pelunasanpiutangheader NOCHECK CONSTRAINT pelunasanpiutangheader_notakredit_nobukti_foreign");
        DB::statement("ALTER TABLE pelunasanpiutangheader NOCHECK CONSTRAINT pelunasanpiutangheader_notadebet_nobukti_foreign");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pelunasanpiutangheader');
    }
}
