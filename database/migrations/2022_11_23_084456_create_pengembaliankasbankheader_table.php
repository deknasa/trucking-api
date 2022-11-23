<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePengembaliankasbankheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::dropIfExists('pengembaliankasbankheader');

        Schema::create('pengembaliankasbankheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->date('tglbukti')->default('1900/1/1');
            $table->longText('keterangan')->default('');
            $table->integer('statusjenistransaksi')->Length(11)->default('0');
            $table->string('postingdari',50)->default('');
            $table->integer('statusapproval')->Length(11)->default('0');
            $table->string('dibayarke',250)->default('');
            $table->unsignedBigInteger('cabang_id')->default('0');
            $table->unsignedBigInteger('bank_id')->default('0');
            $table->string('userapproval',50)->default('');
            $table->date('tglapproval')->default('1900/1/1');
            $table->string('transferkeac',100)->default('');
            $table->string('transferkean',100)->default('');
            $table->string('transferkebank',100)->default('');
            $table->unsignedBigInteger('statusformat')->default(0);
            $table->string('modifiedby',50)->default('');
            $table->timestamps();

            $table->foreign('cabang_id', 'pengembaliankasbankheader_cabang_cabang_id_foreign')->references('id')->on('cabang');
            $table->foreign('bank_id', 'pengembaliankasbankheader_bank_bank_id_foreign')->references('id')->on('bank');            
        });

        DB::statement("ALTER TABLE pengembaliankasbankheader NOCHECK CONSTRAINT pengembaliankasbankheader_cabang_cabang_id_foreign");
        DB::statement("ALTER TABLE pengembaliankasbankheader NOCHECK CONSTRAINT pengembaliankasbankheader_bank_bank_id_foreign");        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pengembaliankasbankheader');
    }
}
