<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateNotakreditheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('notakreditheader');

        Schema::create('notakreditheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->string('pelunasanpiutang_nobukti',50)->nullable();
            $table->longText('keterangan')->nullable();            
            $table->unsignedBigInteger('pelanggan_id')->nullable();
            $table->unsignedBigInteger('agen_id')->nullable();            
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->unsignedBigInteger('alatbayar_id')->nullable();
            $table->string('nowarkat', 50)->nullable();
            $table->string('pengeluaran_nobukti', 50)->nullable();            
            $table->date('tglbukti')->nullable();
            $table->string('postingdari',50)->nullable();
            $table->integer('statusapproval')->length(11)->nullable();
            $table->date('tgllunas')->nullable();
            $table->string('userapproval',50)->nullable();
            $table->date('tglapproval')->nullable();
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

            $table->foreign('pelunasanpiutang_nobukti', 'notakreditheader_pelunasanpiutangheader_pelunasanpiutang_nobukti_foreign')->references('nobukti')->on('pelunasanpiutangheader');   


        });
        DB::statement("ALTER TABLE notakreditheader NOCHECK CONSTRAINT notakreditheader_pelunasanpiutangheader_pelunasanpiutang_nobukti_foreign");


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notakreditheader');
    }
}
