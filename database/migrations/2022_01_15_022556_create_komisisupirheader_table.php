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
            $table->string('nobukti',50)->unique();
            $table->date('tgl')->default('1900/1/1');
            $table->unsignedBigInteger('bank_id')->default('0');
            $table->double('potonganpinjaman',15,2)->default('0');
            $table->double('potonganpinjamansemua',15,2)->default('0');
            $table->double('deposito',15,2)->default('0');
            $table->longText('keterangan')->default('');
            $table->date('tgldari')->default('1900/1/1');
            $table->date('tglsampai')->default('1900/1/1');
            $table->integer('postingborongan')->length(11)->default('0');
            $table->string('nobuktipostingborongan',50)->default('');
            $table->longText('keteranganpostingborongan')->default('');
            $table->integer('postingdeposito')->length(11)->default('0');
            $table->string('nobuktipostingdeposito',50)->default('');
            $table->longText('keteranganpostingdeposito')->default('');
            $table->integer('postingpinjaman')->length(11)->default('0');
            $table->string('nobuktipostingpinjaman',50)->default('');
            $table->longText('keteranganpostingpinjaman')->default('');
            $table->integer('statusapproval')->length(11)->default('0');
            $table->string('userapproval',50)->default('');
            $table->date('tglapproval')->default('1900/1/1');
            $table->date('periode')->default('1900/1/1');
            $table->double('komisisupir',15,2)->default('0');
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
