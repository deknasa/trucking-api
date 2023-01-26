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
            $table->unsignedBigInteger('prosesuangjalansupir_id')->default('0');
            $table->string('nobukti', 50)->default('');
            $table->unsignedBigInteger('penerimaantrucking_bank_id')->default(0);
            $table->date('penerimaantrucking_tglbukti')->default('1900/1/1');
            $table->string('penerimaantrucking_nobukti', 50)->default('');
            $table->unsignedBigInteger('pengeluarantrucking_bank_id')->default(0);
            $table->date('pengeluarantrucking_tglbukti')->default('1900/1/1');
            $table->string('pengeluarantrucking_nobukti', 50)->default('');
            $table->unsignedBigInteger('statusprosesuangjalan')->default(0);
            $table->double('nominal', 15, 2)->default('0');
            $table->longText('keterangan')->default('');
            $table->string('modifiedby', 50)->default('');
            $table->timestamps();

            $table->foreign('prosesuangjalansupir_id', 'prosesuangjalansupirdetail_jurnalumumheader_jurnalumum_id_foreign')->references('id')->on('jurnalumumheader')->onDelete('cascade');
            $table->foreign('penerimaantrucking_bank_id', 'prosesuangjalansupirdetail_penerimaanbank_bank_id_foreign')->references('id')->on('bank');
            $table->foreign('pengeluarantrucking_bank_id', 'prosesuangjalansupirdetail_pengeluaranbank_bank_id_foreign')->references('id')->on('bank');
            $table->foreign('penerimaantrucking_nobukti', 'prosesuangjalansupirdetail_penerimaantrucking_nobukti_foreign')->references('nobukti')->on('penerimaantruckingheader');
            $table->foreign('pengeluarantrucking_nobukti', 'prosesuangjalansupirdetail_pengeluarantrucking_nobukti_foreign')->references('nobukti')->on('pengeluarantruckingheader');
        });

        DB::statement("ALTER TABLE prosesuangjalansupirdetail NOCHECK CONSTRAINT prosesuangjalansupirdetail_penerimaanbank_bank_id_foreign");
        DB::statement("ALTER TABLE prosesuangjalansupirdetail NOCHECK CONSTRAINT prosesuangjalansupirdetail_pengeluaranbank_bank_id_foreign");
        DB::statement("ALTER TABLE prosesuangjalansupirdetail NOCHECK CONSTRAINT prosesuangjalansupirdetail_penerimaantrucking_nobukti_foreign");
        DB::statement("ALTER TABLE prosesuangjalansupirdetail NOCHECK CONSTRAINT prosesuangjalansupirdetail_pengeluarantrucking_nobukti_foreign");
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
