<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePenerimaantruckingheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('penerimaantruckingheader');

        Schema::create('penerimaantruckingheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti', 50)->unique();
            $table->date('tglbukti')->nullable();
            $table->longText('keterangan')->nullable();
            $table->unsignedBigInteger('penerimaantrucking_id')->nullable();
            $table->string('pendapatansupir_bukti', 50)->nullable();
            $table->unsignedBigInteger('karyawan_id')->nullable();
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->unsignedBigInteger('supir_id')->nullable();
            $table->unsignedBigInteger('jenisorder_id')->nullable();
            $table->date('periodedari')->nullable();
            $table->date('periodesampai')->nullable();
            $table->string('coa', 50)->nullable();
            $table->string('penerimaan_nobukti', 50)->nullable();
            $table->unsignedBigInteger('statusformat')->nullable();
            $table->integer('statuscetak')->Length(11)->nullable();
            $table->string('userbukacetak', 50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->Length(11)->nullable();
            $table->integer('statusapprovaledit')->Length(11)->nullable();
            $table->string('userapprovaledit', 50)->nullable();
            $table->date('tglapprovaledit')->nullable();
            $table->dateTime('tglbatasedit')->nullable();
            $table->integer('statuskirimberkas')->Length(11)->nullable();
            $table->string('userkirimberkas',50)->nullable();
            $table->date('tglkirimberkas')->nullable();
            $table->integer('statuskirimberkas')->Length(11)->nullable();
            $table->string('userkirimberkas',50)->nullable();
            $table->date('tglkirimberkas')->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->string('editing_by',50)->nullable();            
            $table->dateTime('editing_at')->nullable();                      
            $table->timestamps();

            $table->foreign('penerimaantrucking_id', 'penerimaantruckingheader_penerimaantrucking_penerimaantrucking_id_foreign')->references('id')->on('penerimaantrucking');
            $table->foreign('bank_id', 'penerimaantruckingheader_bank_bank_id_foreign')->references('id')->on('bank');
            $table->foreign('supir_id', 'penerimaantruckingheader_supir_supir_id_foreign')->references('id')->on('supir');
            $table->foreign('penerimaan_nobukti', 'penerimaantruckingheader_penerimaanheader_penerimaan_nobukti_foreign')->references('nobukti')->on('penerimaanheader');
            $table->foreign('coa', 'penerimaantruckingheader_akunpusat_coa_foreign')->references('coa')->on('akunpusat');
        });

        DB::statement("ALTER TABLE penerimaantruckingheader NOCHECK CONSTRAINT penerimaantruckingheader_penerimaantrucking_penerimaantrucking_id_foreign");
        DB::statement("ALTER TABLE penerimaantruckingheader NOCHECK CONSTRAINT penerimaantruckingheader_bank_bank_id_foreign");
        DB::statement("ALTER TABLE penerimaantruckingheader NOCHECK CONSTRAINT penerimaantruckingheader_supir_supir_id_foreign");
        DB::statement("ALTER TABLE penerimaantruckingheader NOCHECK CONSTRAINT penerimaantruckingheader_penerimaanheader_penerimaan_nobukti_foreign");
        DB::statement("ALTER TABLE penerimaantruckingheader NOCHECK CONSTRAINT penerimaantruckingheader_akunpusat_coa_foreign");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('penerimaantruckingheader');
    }
}
