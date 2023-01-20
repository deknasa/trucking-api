<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePengeluaranheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::dropIfExists('pengeluaranheader');
        
        Schema::create('pengeluaranheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->date('tglbukti')->default('1900/1/1');
            $table->unsignedBigInteger('pelanggan_id')->default('0');
            $table->unsignedBigInteger('alatbayar_id')->default('0');
            $table->string('postingdari',50)->default('');
            $table->integer('statusapproval')->Length(11)->default('0');
            $table->string('dibayarke',250)->default('');
            $table->unsignedBigInteger('bank_id')->default('0');
            $table->string('userapproval',50)->default('');
            $table->date('tglapproval')->default('1900/1/1');
            $table->integer('statuscetak')->Length(11)->default('0');
            $table->string('userbukacetak',50)->default('');
            $table->date('tglbukacetak')->default('1900/1/1');
            $table->integer('jumlahcetak')->Length(11)->default('0');
            $table->string('transferkeac',100)->default('');
            $table->string('transferkean',100)->default('');
            $table->string('transferkebank',100)->default('');
            $table->unsignedBigInteger('statusformat')->default(0);
            $table->string('modifiedby',50)->default('');
            $table->timestamps();


            $table->foreign('pelanggan_id', 'pengeluaranheader_pelanggan_pelanggan_id_foreign')->references('id')->on('pelanggan');
            $table->foreign('alatbayar_id', 'pengeluaranheader_alatbayar_alatbayar_id_foreign')->references('id')->on('alatbayar');
            $table->foreign('bank_id', 'pengeluaranheader_bank_bank_id_foreign')->references('id')->on('bank');


        });

        DB::statement("ALTER TABLE pengeluaranheader NOCHECK CONSTRAINT pengeluaranheader_pelanggan_pelanggan_id_foreign");
        DB::statement("ALTER TABLE pengeluaranheader NOCHECK CONSTRAINT pengeluaranheader_alatbayar_alatbayar_id_foreign");
        DB::statement("ALTER TABLE pengeluaranheader NOCHECK CONSTRAINT pengeluaranheader_bank_bank_id_foreign");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pengeluaranheader');
    }
}
