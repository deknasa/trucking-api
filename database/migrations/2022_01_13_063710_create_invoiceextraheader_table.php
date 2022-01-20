<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceextraheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoiceextraheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti', 50)->unique();
            $table->date('tgl')->default('1900/1/1');
            $table->unsignedBigInteger('pelanggan_id')->default('0');
            $table->double('nominal')->default('0');
            $table->longText('keterangan')->default('');
            $table->string('modifiedby', 50)->default('');
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
        Schema::dropIfExists('invoiceextraheader');
    }
}
