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
            $table->date('tglbukti')->nullable();
            $table->longText('keterangan')->nullable();            
            $table->longText('penerima')->nullable();            
            $table->unsignedBigInteger('penerima_id')->nullable();
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->string('pengeluaran_nobukti',50)->nullable();
            $table->string('coakaskeluar',50)->nullable();
            $table->string('postingdari',50)->nullable();
            $table->date('tglkaskeluar')->nullable();
            $table->unsignedBigInteger('statusformat')->nullable();
            $table->integer('statuscetak')->Length(11)->nullable();
            $table->string('userbukacetak',50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->Length(11)->nullable();
            $table->integer('statuskirimberkas')->Length(11)->nullable();
            $table->string('userkirimberkas',50)->nullable();
            $table->date('tglkirimberkas')->nullable();

            $table->longText('info')->nullable();
            $table->string('modifiedby',50)->nullable();
            $table->string('editing_by',50)->nullable();            
            $table->dateTime('editing_at')->nullable();            
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
