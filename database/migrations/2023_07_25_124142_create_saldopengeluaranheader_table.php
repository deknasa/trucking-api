<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateSaldopengeluaranheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('saldopengeluaranheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->date('tglbukti')->nullable();
            $table->longText('keterangan')->nullable();            
            $table->unsignedBigInteger('pelanggan_id')->nullable();
            $table->unsignedBigInteger('alatbayar_id')->nullable();
            $table->string('postingdari',50)->nullable();
            $table->integer('statusapproval')->Length(11)->nullable();
            $table->string('dibayarke',250)->nullable();
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->string('userapproval',50)->nullable();
            $table->date('tglapproval')->nullable();
            $table->integer('statuscetak')->Length(11)->nullable();
            $table->string('userbukacetak',50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->Length(11)->nullable();
            $table->string('transferkeac',100)->nullable();
            $table->string('transferkean',100)->nullable();
            $table->string('transferkebank',100)->nullable();
            $table->unsignedBigInteger('statusformat')->nullable();
            $table->string('modifiedby',50)->nullable();
            $table->timestamps();

            $table->foreign('pelanggan_id', 'saldopengeluaranheader_pelanggan_pelanggan_id_foreign')->references('id')->on('pelanggan');
            $table->foreign('alatbayar_id', 'saldopengeluaranheader_alatbayar_alatbayar_id_foreign')->references('id')->on('alatbayar');
            $table->foreign('bank_id', 'saldopengeluaranheader_bank_bank_id_foreign')->references('id')->on('bank');
        });

        DB::statement("ALTER TABLE saldopengeluaranheader NOCHECK CONSTRAINT saldopengeluaranheader_pelanggan_pelanggan_id_foreign");
        DB::statement("ALTER TABLE saldopengeluaranheader NOCHECK CONSTRAINT saldopengeluaranheader_alatbayar_alatbayar_id_foreign");
        DB::statement("ALTER TABLE saldopengeluaranheader NOCHECK CONSTRAINT saldopengeluaranheader_bank_bank_id_foreign");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('saldopengeluaranheader');
    }
}
