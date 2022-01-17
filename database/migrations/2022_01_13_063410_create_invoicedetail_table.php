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
            $table->string('nobukti', 50)->default('');
            $table->unsignedBigInteger('invoice_id')->default('0');
            $table->string('extra_nobukti', 50)->default('');
            $table->double('nominal', 15,2)->default('0');
            $table->string('keterangan', 500)->default('');
            $table->string('modifiedby', 50)->default('');
            $table->unsignedBigInteger('suratpengantar_id')->default('0');
            $table->string('suratpengantar_nobukti', 50)->default('');
            $table->timestamps();

            $table->foreign('invoice_id')->references('id')->on('invoiceheader')->onDelete('cascade');            
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
