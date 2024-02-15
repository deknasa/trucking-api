<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateBankTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::dropIfExists('bank');

        Schema::create('bank', function (Blueprint $table) {
            $table->id();            
            $table->string('kodebank', 50)->nullable();
            $table->string('namabank', 50)->nullable();
            $table->string('coa', 50)->nullable();
            $table->string('tipe', 50)->nullable();
            $table->integer('statusdefault')->length(11)->nullable();
            $table->integer('statusaktif')->length(11)->nullable();
            $table->integer('formatpenerimaan')->length(11)->nullable();
            $table->integer('formatpengeluaran')->length(11)->nullable();
            $table->unsignedBigInteger('tas_id')->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->timestamps();

            $table->foreign('coa', 'bank_akunpusat_coa_foreign')->references('coa')->on('akunpusat');

         });

         DB::statement("ALTER TABLE bank NOCHECK CONSTRAINT bank_akunpusat_coa_foreign");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bank');
    }
}
