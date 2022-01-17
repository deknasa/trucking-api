<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKomisisupirheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('komisisupirheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->default('');
            $table->date('tgl')->default('1900/1/1');
            $table->unsignedBigInteger('bank_id')->default('0');
            $table->double('upotpinjaman',15,2)->default('0');
            $table->double('upotpinjamansemua',15,2)->default('0');
            $table->double('udeposito',15,2)->default('0');
            $table->string('keterangan',250)->default('');
            $table->date('tgldr')->default('1900/1/1');
            $table->date('tglsd')->default('1900/1/1');
            $table->integer('postuborongan')->length(11)->default('0');
            $table->string('npostuborongan',50)->default('');
            $table->string('ketpostuborongan',250)->default('');
            $table->integer('postdeposit')->length(11)->default('0');
            $table->string('npostdeposit',50)->default('');
            $table->string('ketpostdeposit',250)->default('');
            $table->integer('postpinjaman')->length(11)->default('0');
            $table->string('npostpinjaman',50)->default('');
            $table->string('ketpostpinjaman',250)->default('');
            $table->integer('app]')->length(11)->default('0');
            $table->date('periode')->default('1900/1/1');
            $table->string('appuserid',50)->default('');
            $table->date('appdate')->default('1900/1/1');
            $table->double('ukomisisupir',15,2)->default('0');
            $table->string('modifiedby',50)->default('');
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
        Schema::dropIfExists('komisisupirheader');
    }
}
