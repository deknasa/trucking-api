<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePenerimaangirodetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::dropIfExists('penerimaangirodetail');

        Schema::create('penerimaangirodetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('penerimaangiro_id')->nullable();
            $table->string('nobukti',50)->nullable();
            $table->string('nowarkat',50)->nullable();
            $table->date('tgljatuhtempo')->nullable();
            $table->double('nominal',15,2)->nullable();
            $table->string('coadebet',50)->nullable();
            $table->string('coakredit',50)->nullable();
            $table->longText('keterangan')->nullable();
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->unsignedBigInteger('pelanggan_id')->nullable();
            $table->string('invoice_nobukti',50)->nullable();
            $table->unsignedBigInteger('bankpelanggan_id')->nullable();
            $table->string('jenisbiaya',50)->nullable();
            $table->string('pelunasanpiutang_nobukti',50)->nullable();
            $table->date('bulanbeban')->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby',50)->nullable();
            $table->timestamps();


            $table->foreign('penerimaangiro_id', 'penerimaangirodetail_penerimaangiroheader_penerimaangiro_id_foreign')->references('id')->on('penerimaangiroheader')->onDelete('cascade');    
            $table->foreign('bank_id', 'penerimaangirodetail_bank_bank_id_foreign')->references('id')->on('bank');
            $table->foreign('pelanggan_id', 'penerimaangirodetail_pelanggan_pelanggan_id_foreign')->references('id')->on('pelanggan');
            $table->foreign('invoice_nobukti', 'penerimaangirodetail_invoiceheader_invoice_nobukti_foreign')->references('nobukti')->on('invoiceheader');
            $table->foreign('bankpelanggan_id', 'penerimaangirodetail_bankpelanggan_bankpelanggan_id_foreign')->references('id')->on('bankpelanggan');
            $table->foreign('pelunasanpiutang_nobukti', 'penerimaangirodetail_pelunasanpiutangheader_pelunasanpiutang_nobukti_foreign')->references('nobukti')->on('pelunasanpiutangheader');


            
        });

        DB::statement("ALTER TABLE penerimaangirodetail NOCHECK CONSTRAINT penerimaangirodetail_bank_bank_id_foreign");
        DB::statement("ALTER TABLE penerimaangirodetail NOCHECK CONSTRAINT penerimaangirodetail_pelanggan_pelanggan_id_foreign");
        DB::statement("ALTER TABLE penerimaangirodetail NOCHECK CONSTRAINT penerimaangirodetail_invoiceheader_invoice_nobukti_foreign");
        DB::statement("ALTER TABLE penerimaangirodetail NOCHECK CONSTRAINT penerimaangirodetail_bankpelanggan_bankpelanggan_id_foreign");
        DB::statement("ALTER TABLE penerimaangirodetail NOCHECK CONSTRAINT penerimaangirodetail_pelunasanpiutangheader_pelunasanpiutang_nobukti_foreign");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('penerimaangirodetail');
    }
}
