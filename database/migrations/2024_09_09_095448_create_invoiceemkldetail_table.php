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
            $table->unsignedBigInteger('invoiceemkl_id')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->string('jobemkl_nobukti', 50)->nullable();
            $table->unsignedBigInteger('container_id')->nullable();
            $table->double('nominal', 15,2)->nullable();
            $table->double('selisih', 15,2)->nullable();
            $table->longText('keterangan')->nullable();
            $table->string('coadebet', 50)->nullable();
            $table->string('coakredit', 50)->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->timestamps();

            $table->foreign('invoiceemkl_id', 'invoiceemkldetail_invoiceemklheader_invoiceemkl_idforeign')->references('id')->on('invoiceemklheader')->onDelete('cascade');    
            $table->foreign('jobemkl_nobukti', 'invoiceemkldetail_jobemkl_jobemkl_nobukti_foreign')->references('nobukti')->on('jobemkl');
            $table->foreign('container_id', 'invoiceemkldetail_container_container_id_foreign')->references('id')->on('container');

        });

        DB::statement("ALTER TABLE invoiceemkldetail NOCHECK CONSTRAINT invoiceemkldetail_jobemkl_jobemkl_nobukti_foreign");
        DB::statement("ALTER TABLE invoiceemkldetail NOCHECK CONSTRAINT invoiceemkldetail_container_container_id_foreign");

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
