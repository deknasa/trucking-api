<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceextradetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('invoiceextradetail');

        Schema::create('invoiceextradetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoiceextra_id')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->double('nominal', 15,2)->nullable();
            $table->longText('keterangan')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->timestamps();

            $table->foreign('invoiceextra_id', 'invoiceextradetail_invoiceextraheader_invoiceextra_id_foreign')->references('id')->on('invoiceextraheader')->onDelete('cascade');    

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoiceextradetail');
    }
}
