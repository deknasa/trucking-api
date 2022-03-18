<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKasgantungheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kasgantungheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->date('tgl')->default('1900/1/1');
            $table->unsignedBigInteger('penerima_id')->default('0');
            $table->longText('keterangan')->default('');
            $table->unsignedBigInteger('bank_id')->default('0');
            $table->string('nobuktikaskeluar',50)->default('');
            $table->string('coakaskeluar',50)->default('');
            $table->string('postingdari',50)->default('');
            $table->date('tglkaskeluar')->default('1900/1/1');
            $table->string('modifiedby',50)->default('');
            $table->timestamps();

            $table->foreign('penerima_id')->references('id')->on('penerima');
            $table->foreign('bank_id')->references('id')->on('bank');
            $table->foreign('coakaskeluar')->references('coa')->on('akunpusat');
            $table->foreign('nobukti')->references('kasgantung_nobukti')->on('absensisupirheader');

        });
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
