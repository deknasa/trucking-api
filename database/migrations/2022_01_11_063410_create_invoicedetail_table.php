<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicedetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoicedetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id')->default('0');
            $table->string('nobukti', 50)->default('');
            $table->string('extra_nobukti', 50)->default('');
            $table->double('nominal', 15,2)->default('0');
            $table->longText('keterangan')->default('');
            $table->string('modifiedby', 50)->default('');
            $table->unsignedBigInteger('suratpengantar_id')->default('0');
            $table->string('orderantrucking_nobukti', 50)->default('');
            $table->timestamps();

            $table->foreign('invoice_id')->references('id')->on('invoiceheader')->onDelete('cascade');            
            $table->foreign('orderantrucking_nobukti')->references('nobukti')->on('orderantrucking');
            $table->foreign('suratpengantar_id')->references('id')->on('suratpengantar');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoicedetail');
    }
}
