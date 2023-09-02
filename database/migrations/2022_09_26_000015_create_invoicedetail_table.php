<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateInvoicedetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::dropIfExists('invoicedetail');

        Schema::create('invoicedetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->double('nominal', 15,2)->nullable();
            $table->double('nominalextra', 15,2)->nullable();
            $table->double('nominalretribusi', 15,2)->nullable();
            $table->double('total', 15,2)->nullable();
            $table->longText('keterangan')->nullable();
            $table->string('orderantrucking_nobukti', 50)->nullable();
            $table->longText('suratpengantar_nobukti')->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->timestamps();

            $table->foreign('invoice_id', 'invoicedetail_invoiceheader_invoice_idforeign')->references('id')->on('invoiceheader')->onDelete('cascade');    
            $table->foreign('orderantrucking_nobukti', 'invoicedetail_orderantrucking_orderantrucking_nobukti_foreign')->references('nobukti')->on('orderantrucking');


        });

        
        DB::statement("ALTER TABLE invoicedetail NOCHECK CONSTRAINT invoicedetail_orderantrucking_orderantrucking_nobukti_foreign");
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
