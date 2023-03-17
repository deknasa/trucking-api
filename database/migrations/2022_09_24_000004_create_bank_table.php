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
            $table->string('kodebank', 50)->default('');
            $table->string('namabank', 50)->default('');
            $table->string('coa', 50)->default('');
            $table->string('tipe', 50)->default('');
            $table->integer('statusdefault')->length(11)->default(0);
            $table->integer('statusaktif')->length(11)->default(0);
            $table->integer('formatpenerimaan')->length(11)->default(0);
            $table->integer('formatpengeluaran')->length(11)->default(0);
            $table->string('modifiedby', 50)->default('');
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
