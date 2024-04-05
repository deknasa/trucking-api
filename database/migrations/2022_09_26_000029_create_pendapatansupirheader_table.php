<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePendapatansupirheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('pendapatansupirheader');

        Schema::create('pendapatansupirheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->date('tglbukti')->nullable();
            $table->longText('keterangan')->nullable();            
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->unsignedBigInteger('supir_id')->nullable();
            $table->date('tgldari')->nullable();
            $table->date('tglsampai')->nullable();
            $table->string('pengeluaran_nobukti', 50)->nullable();            
            $table->string('coa',50)->nullable();            
            $table->unsignedBigInteger('alatbayar_id')->nullable();
            $table->integer('statusapproval')->length(11)->nullable();
            $table->string('userapproval',50)->nullable();
            $table->date('tglapproval')->nullable();
            $table->date('periode')->nullable();
            $table->unsignedBigInteger('statusformat')->nullable();
            $table->integer('statuscetak')->Length(11)->nullable();
            $table->string('userbukacetak',50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->Length(11)->nullable();
            $table->longText('info')->nullable();
            $table->integer('statuskirimberkas')->Length(11)->nullable();
            $table->string('userkirimberkas',50)->nullable();
            $table->date('tglkirimberkas')->nullable();

            $table->string('modifiedby',50)->nullable();            
            $table->string('editing_by',50)->nullable();            
            $table->dateTime('editing_at')->nullable();                      
            $table->timestamps();

            $table->foreign('bank_id', 'pendapatansupirheader_bank_bank_id_foreign')->references('id')->on('bank');   

        });
        DB::statement("ALTER TABLE pendapatansupirheader NOCHECK CONSTRAINT pendapatansupirheader_bank_bank_id_foreign");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pendapatansupirheader');
    }
}
