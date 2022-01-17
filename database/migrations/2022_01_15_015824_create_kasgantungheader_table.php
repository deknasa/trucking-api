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
            $table->string('nobukti',50)->default('');
            $table->date('tgl')->default('1900/1/1');
            $table->unsignedBigInteger('penerima_id')->default('0');
            $table->string('keterangan',250)->default('');
            $table->string('modifiedby',50)->default('');
            $table->unsignedBigInteger('bank_id')->default('0');
            $table->string('npostkaskeluar',50)->default('');
            $table->string('coakaskeluar',50)->default('');
            $table->integer('status')->length(11)->default('0');
            $table->string('postfrom',50)->default('');
            $table->date('tglPostkaskeluar')->default('1900/1/1');
            $table->timestamps();
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
