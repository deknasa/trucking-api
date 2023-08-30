<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateProsesuangjalansupirdetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::dropIfExists('prosesuangjalansupirdetail');

        Schema::create('prosesuangjalansupirdetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prosesuangjalansupir_id')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->unsignedBigInteger('penerimaantrucking_bank_id')->nullable();
            $table->date('penerimaantrucking_tglbukti')->nullable();
            $table->string('penerimaantrucking_nobukti', 50)->nullable();
            $table->unsignedBigInteger('pengeluarantrucking_bank_id')->nullable();
            $table->date('pengeluarantrucking_tglbukti')->nullable();
            $table->string('pengeluarantrucking_nobukti', 50)->nullable();
            $table->unsignedBigInteger('pengembaliankasgantung_bank_id')->nullable();
            $table->date('pengembaliankasgantung_tglbukti')->nullable();
            $table->string('pengembaliankasgantung_nobukti', 50)->nullable();
            $table->unsignedBigInteger('statusprosesuangjalan')->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->longText('keterangan')->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->timestamps();

            $table->foreign('prosesuangjalansupir_id', 'prosesuangjalansupirdetail_jurnalumumheader_jurnalumum_id_foreign')->references('id')->on('prosesuangjalansupirheader')->onDelete('cascade');
            $table->foreign('penerimaantrucking_bank_id', 'prosesuangjalansupirdetail_penerimaanbank_bank_id_foreign')->references('id')->on('bank');
            $table->foreign('pengeluarantrucking_bank_id', 'prosesuangjalansupirdetail_pengeluaranbank_bank_id_foreign')->references('id')->on('bank');
            $table->foreign('pengembaliankasgantung_bank_id', 'prosesuangjalansupirdetail_pengembaliankasgantung_bank_id_foreign')->references('id')->on('bank');
            $table->foreign('penerimaantrucking_nobukti', 'prosesuangjalansupirdetail_penerimaantrucking_nobukti_foreign')->references('nobukti')->on('penerimaantruckingheader');
            $table->foreign('pengeluarantrucking_nobukti', 'prosesuangjalansupirdetail_pengeluarantrucking_nobukti_foreign')->references('nobukti')->on('pengeluarantruckingheader');
            $table->foreign('pengembaliankasgantung_nobukti', 'prosesuangjalansupirdetail_pengembaliankasgantung_nobukti_foreign')->references('nobukti')->on('pengeluarantruckingheader');
        });

        DB::statement("ALTER TABLE prosesuangjalansupirdetail NOCHECK CONSTRAINT prosesuangjalansupirdetail_penerimaanbank_bank_id_foreign");
        DB::statement("ALTER TABLE prosesuangjalansupirdetail NOCHECK CONSTRAINT prosesuangjalansupirdetail_pengeluaranbank_bank_id_foreign");
        DB::statement("ALTER TABLE prosesuangjalansupirdetail NOCHECK CONSTRAINT prosesuangjalansupirdetail_pengembaliankasgantung_bank_id_foreign");
        DB::statement("ALTER TABLE prosesuangjalansupirdetail NOCHECK CONSTRAINT prosesuangjalansupirdetail_penerimaantrucking_nobukti_foreign");
        DB::statement("ALTER TABLE prosesuangjalansupirdetail NOCHECK CONSTRAINT prosesuangjalansupirdetail_pengeluarantrucking_nobukti_foreign");
        DB::statement("ALTER TABLE prosesuangjalansupirdetail NOCHECK CONSTRAINT prosesuangjalansupirdetail_pengembaliankasgantung_nobukti_foreign");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prosesuangjalansupirdetail');
    }
}
