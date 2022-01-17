<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoiceheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti', 50)->default('');
            $table->date('tgl')->default('1900/1/1');
            $table->double('nominal',15,2)->default('0');
            $table->string('keterangan', 250)->default('');
            $table->date('tglterima')->default('1900/1/1');
            $table->date('tgljt')->default('1900/1/1');
            $table->unsignedBigInteger('emkl_id')->default('0');
            $table->unsignedBigInteger('jenisorderan_id')->default('0');
            $table->unsignedBigInteger('cabang_id')->default('0');
            $table->string('piutang_nobukti', 50)->default('');
            $table->integer('statusapproval')->length(11)->default('0');
            $table->string('appuserid', 50)->default('');
            $table->date('appdate')->default('1900/1/1');
            $table->string('jnsinvoice', 50)->default('');
            $table->string('modifiedby', 50)->default('');
            $table->string('invoiceextra_nobukti', 50)->default('');
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
        Schema::dropIfExists('invoiceheader');
    }
}
