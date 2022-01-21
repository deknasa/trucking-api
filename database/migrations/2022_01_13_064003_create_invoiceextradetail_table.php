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
        Schema::create('invoiceextradetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoiceextra_id')->default(0);
            $table->string('nobukti', 50)->default('');
            $table->date('tgl')->default('1900/1/1');
            $table->date('qty',15,2)->default('0');
            $table->date('hrgsat',15,2)->default('0');
            $table->date('total',15,2)->default('0');
            $table->string('persentasedisc', 50)->default('');
            $table->date('nominaldisc',15,2)->default('0');
            $table->date('biaya',15,2)->default('0');
            $table->date('nominal',15,2)->default('0');
            $table->string('keterangan', 250)->default('');
            $table->string('modifiedby', 50)->default('');
            $table->timestamps();

            $table->foreign('invoiceextra_id')->references('id')->on('invoiceextraheader')->onDelete('cascade');            
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
