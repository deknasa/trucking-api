<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateKasgantungheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::dropIfExists('kasgantungheader');

        Schema::create('kasgantungheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->date('tglbukti')->default('1900/1/1');
            $table->unsignedBigInteger('penerima_id')->default('0');
            $table->unsignedBigInteger('bank_id')->default('0');
            $table->string('pengeluaran_nobukti',50)->default('');
            $table->string('coakaskeluar',50)->default('');
            $table->string('postingdari',50)->default('');
            $table->date('tglkaskeluar')->default('1900/1/1');
            $table->unsignedBigInteger('statusformat')->default(0);
            $table->integer('statuscetak')->Length(11)->default('0');
            $table->string('userbukacetak',50)->default('');
            $table->date('tglbukacetak')->default('1900/1/1');
            $table->integer('jumlahcetak')->Length(11)->default('0');
            $table->string('modifiedby',50)->default('');
            $table->timestamps();

            $table->foreign('penerima_id', 'kasgantungheader_penerima_penerima_id_foreign')->references('id')->on('penerima');
            $table->foreign('bank_id', 'kasgantungheader_bank_bank_id_foreign')->references('id')->on('bank');
            $table->foreign('coakaskeluar', 'kasgantungheader_akunpusat_coakaskeluar_foreign')->references('coa')->on('akunpusat');
            $table->foreign('pengeluaran_nobukti', 'kasgantungheader_pengeluaranheader_pengeluaran_nobukti_foreign')->references('nobukti')->on('pengeluaranheader');

        });

        DB::statement("ALTER TABLE kasgantungheader NOCHECK CONSTRAINT kasgantungheader_penerima_penerima_id_foreign");
        DB::statement("ALTER TABLE kasgantungheader NOCHECK CONSTRAINT kasgantungheader_bank_bank_id_foreign");
        DB::statement("ALTER TABLE kasgantungheader NOCHECK CONSTRAINT kasgantungheader_akunpusat_coakaskeluar_foreign");
        DB::statement("ALTER TABLE kasgantungheader NOCHECK CONSTRAINT kasgantungheader_pengeluaranheader_pengeluaran_nobukti_foreign");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kasgantungheader');
    }
}
