<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateInvoiceemkldetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('invoiceemkldetail');

        Schema::create('invoiceemkldetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->string('jobemkl_nobukti', 50)->nullable();
            $table->double('nominal', 15,2)->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->timestamps();

            $table->foreign('invoiceemkl_id', 'invoiceemkldetail_invoiceemklheader_invoiceemkl_idforeign')->references('id')->on('invoiceemklheader')->onDelete('cascade');    
            $table->foreign('jobemkl_nobukti', 'invoiceemkldetail_jobemkl_jobemkl_nobukti_foreign')->references('nobukti')->on('jobemkl');

        });

        DB::statement("ALTER TABLE invoiceemkldetail NOCHECK CONSTRAINT invoiceemkldetail_jobemkl_jobemkl_nobukti_foreign");

    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoiceemkldetail');
    }
}
