<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateInvoiceextraheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::dropIfExists('invoiceextraheader');

        Schema::create('invoiceextraheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti', 50)->unique();
            $table->date('tglbukti')->default('1900/1/1');
            $table->unsignedBigInteger('pelanggan_id')->default('0');
            $table->unsignedBigInteger('agen_id')->default('0');
            $table->double('nominal')->default('0');
            $table->longText('keterangan')->default('');
            $table->unsignedBigInteger('statusformat')->default(0);
            $table->string('modifiedby', 50)->default('');
            $table->timestamps();

            $table->foreign('pelanggan_id', 'invoiceextraheader_pelanggan_pelanggan_id_foreign')->references('id')->on('pelanggan');
            $table->foreign('agen_id', 'invoiceextraheader_agen_agen_id_foreign')->references('id')->on('agen');


        });

        DB::statement("ALTER TABLE invoiceextraheader NOCHECK CONSTRAINT invoiceextraheader_pelanggan_pelanggan_id_foreign");
        DB::statement("ALTER TABLE invoiceextraheader NOCHECK CONSTRAINT invoiceextraheader_agen_agen_id_foreign");

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
