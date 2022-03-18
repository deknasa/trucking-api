<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProsesgajisupirheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prosesgajisupirheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->date('tgl')->default('1900/1/1');
            $table->unsignedBigInteger('bank_id')->default('0');
            $table->longText('keterangan')->default('');
            $table->date('tgldari')->default('1900/1/1');
            $table->date('tglsampai')->default('1900/1/1');
            $table->integer('statusapproval')->length(11)->default('0');
            $table->string('userapproval',50)->default('');
            $table->date('tglapproval')->default('1900/1/1');
            $table->date('periode')->default('1900/1/1');
            $table->string('modifiedby',50)->default('');
            $table->timestamps();

            $table->foreign('bank_id')->references('id')->on('bank');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prosesgajisupirheader');
    }
}
