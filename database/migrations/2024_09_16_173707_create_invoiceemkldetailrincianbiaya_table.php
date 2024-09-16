<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateInvoiceemkldetailrincianbiayaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoiceemkldetailrincianbiaya', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoiceemkl_id')->nullable();
            $table->unsignedBigInteger('invoiceemkldetail_id')->nullable();
            $table->string('jobemkl_nobukti',500)->nullable();
            $table->unsignedBigInteger('biayaemkl_id')->nullable();
            $table->double('nominal', 15,2)->nullable();
            $table->longText('keterangan')->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby',50)->nullable();            
            $table->timestamps();

            $table->foreign('invoiceemkldetail_id', 'invoiceemkldetailrincianbiaya_invoiceemkldetail_invoiceemkldetail_id_foreign')->references('id')->on('invoiceemkldetail')->onDelete('cascade');       
            // $table->foreign('biayaemkl_id', 'invoiceemkldetailrincianbiaya_biayaemkl_biayaemkl_id_foreign')->references('id')->on('biayaemkl');
            // $table->foreign('jobemkl_nobukti', 'invoiceemkldetailrincianbiaya_jobemkl_jobemkl_nobukti_foreign')->references('nobukti')->on('jobemkl');

        });

        // DB::statement("ALTER TABLE biayaemkl NOCHECK CONSTRAINT invoiceemkldetailrincianbiaya_biayaemkl_biayaemkl_id_foreign");
        // DB::statement("ALTER TABLE jobemkl NOCHECK CONSTRAINT invoiceemkldetailrincianbiaya_jobemkl_jobemkl_nobukti_foreign");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoiceemkldetailrincianbiaya');
    }
}
