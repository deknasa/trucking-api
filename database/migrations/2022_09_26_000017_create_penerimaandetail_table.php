<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePenerimaandetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::dropIfExists('penerimaandetail');

        Schema::create('penerimaandetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('penerimaan_id')->nullable();
            $table->string('nobukti',50)->nullable();
            $table->string('nowarkat',50)->nullable();
            $table->date('tgljatuhtempo')->nullable();
            $table->double('nominal',15,2)->nullable();
            $table->string('coadebet',50)->nullable();
            $table->string('coakredit',50)->nullable();
            $table->longText('keterangan')->nullable();
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->string('invoice_nobukti',50)->nullable();
            $table->unsignedBigInteger('bankpelanggan_id')->nullable();
            $table->string('pelunasanpiutang_nobukti',50)->nullable();
            $table->string('penerimaangiro_nobukti',50)->nullable();
            $table->date('bulanbeban')->nullable();
            $table->string('modifiedby',50)->nullable();
            $table->timestamps();

            $table->foreign('penerimaan_id', 'penerimaandetail_penerimaanheader_penerimaan_id_foreign')->references('id')->on('penerimaanheader')->onDelete('cascade');    
            $table->foreign('bank_id', 'penerimaandetail_bank_bank_id_foreign')->references('id')->on('bank');    
            $table->foreign('bankpelanggan_id', 'penerimaandetail_bankpelanggan_bankpelanggan_id_foreign')->references('id')->on('bankpelanggan');    
            $table->foreign('coadebet', 'penerimaandetail_akunpusat_coadebet_foreign')->references('coa')->on('akunpusat');    
            $table->foreign('coakredit', 'penerimaandetail_akunpusat_coakredit_foreign')->references('coa')->on('akunpusat');    


        });

        DB::statement("ALTER TABLE penerimaandetail NOCHECK CONSTRAINT penerimaandetail_bank_bank_id_foreign");
        DB::statement("ALTER TABLE penerimaandetail NOCHECK CONSTRAINT penerimaandetail_bankpelanggan_bankpelanggan_id_foreign");
        DB::statement("ALTER TABLE penerimaandetail NOCHECK CONSTRAINT penerimaandetail_akunpusat_coadebet_foreign");
        DB::statement("ALTER TABLE penerimaandetail NOCHECK CONSTRAINT penerimaandetail_akunpusat_coakredit_foreign");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('penerimaandetail');
    }
}
