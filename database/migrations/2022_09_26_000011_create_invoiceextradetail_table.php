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
            $table->unsignedBigInteger('invoiceextra_id')->default(0);
            $table->string('nobukti', 50)->default('');
            $table->double('nominal', 15,2)->default(0);
            $table->longText('keterangan')->default('');
            $table->string('modifiedby', 50)->default('');
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
