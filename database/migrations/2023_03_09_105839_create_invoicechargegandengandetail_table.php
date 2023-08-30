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
            $table->unsignedBigInteger('invoicechargegandengan_id')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->string('jobtrucking', 50)->nullable();
            $table->unsignedBigInteger('trado_id')->nullable();
            $table->unsignedBigInteger('gandengan_id')->nullable();
            $table->date('tgltrip')->nullable();
            $table->date('tglakhir')->nullable();
            $table->integer('jumlahhari')->Length(11)->nullable();
            $table->double('nominal',15,2)->nullable();
            $table->double('total',15,2)->nullable();
            $table->longText('keterangan')->nullable();
            $table->string('jenisorder', 500)->nullable();
            $table->string('namagudang', 500)->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby', 50)->nullable();            
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
