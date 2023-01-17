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
            $table->unsignedBigInteger('penerimaan_id')->default('0');
            $table->string('nobukti',50)->default('');
            $table->string('nowarkat',50)->default('');
            $table->date('tgljatuhtempo')->default('1900/1/1');
            $table->double('nominal',15,2)->default('0');
            $table->string('coadebet',50)->default('');
            $table->string('coakredit',50)->default('');
            $table->longText('keterangan')->default('');
            $table->unsignedBigInteger('bank_id')->default('0');
            $table->string('invoice_nobukti',50)->default('');
            $table->unsignedBigInteger('bankpelanggan_id')->default('0');
            $table->string('jenisbiaya',50)->default('');
            $table->string('pelunasanpiutang_nobukti',50)->default('');
            $table->date('bulanbeban')->default('1900/1/1');
            $table->string('modifiedby',50)->default('');
            $table->timestamps();

            $table->foreign('penerimaan_id', 'penerimaandetail_penerimaanheader_penerimaan_id_foreign')->references('id')->on('penerimaanheader')->onDelete('cascade');    
            $table->foreign('bank_id', 'penerimaandetail_bank_bank_id_foreign')->references('id')->on('bank');    
            $table->foreign('bankpelanggan_id', 'penerimaandetail_bankpelanggan_bankpelanggan_id_foreign')->references('id')->on('bankpelanggan');    
            $table->foreign('coadebet', 'penerimaandetail_akunpusat_coadebet_foreign')->references('coa')->on('akunpusat');    
            $table->foreign('coakredit', 'penerimaandetail_akunpusat_coakredit_foreign')->references('coa')->on('akunpusat');    


        });

        DB::statement("ALTER TABLE penerimaandetail NOCHECK CONSTRAINT penerimaandetail_bank_bank_id_foreign");
        DB::statement("ALTER TABLE penerimaandetail NOCHECK CONSTRAINT penerimaandetail_pelanggan_pelanggan_id_foreign");
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
