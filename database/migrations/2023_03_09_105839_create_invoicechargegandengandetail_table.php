<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateInvoicechargegandengandetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoicechargegandengandetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoicechargegandengan_id')->default(0);
            $table->string('nobukti', 50)->default('');
            $table->string('jobtrucking', 50)->default('');
            $table->unsignedBigInteger('trado_id')->default(0);
            $table->date('tgltrip')->default('1900/1/1');
            $table->integer('jumlahhari')->Length(11)->default('0');
            $table->double('nominal',15,2)->default('0');
            $table->double('total',15,2)->default('0');
            $table->longText('keterangan')->default('');

            $table->string('modifiedby', 50)->default('');            
            $table->timestamps();

            $table->foreign('invoicechargegandengan_id', 'invoicechargegandengandetail_invoicechargegandenganheader_invoicechargegandengan_id_foreign')->references('id')->on('invoicechargegandenganheader')->onDelete('cascade');    
            $table->foreign('trado_id', 'invoicechargegandengandetail_trado_trado_id_foreign')->references('id')->on('trado');

        });

        DB::statement("ALTER TABLE invoicechargegandengandetail NOCHECK CONSTRAINT invoicechargegandengandetail_trado_trado_id_foreign");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoicechargegandengandetail');
    }
}
