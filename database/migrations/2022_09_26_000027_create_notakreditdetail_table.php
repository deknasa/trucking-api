<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateNotakreditdetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('notakreditdetail');

        Schema::create('notakreditdetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('notakredit_id')->nullable();
            $table->string('nobukti',50)->nullable();
            $table->date('tglterima')->nullable();
            $table->string('invoice_nobukti',50)->nullable();
            $table->double('nominal',15,2)->nullable();
            $table->double('nominalbayar',15,2)->nullable();
            $table->double('penyesuaian',15,2)->nullable();
            $table->longText('keterangan')->nullable();
            $table->string('coaadjust',50)->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby',50)->nullable();
            $table->timestamps();

            $table->foreign('notakredit_id', 'notakreditdetail_notakreditheader_notakredit_id_foreign')->references('id')->on('notakreditheader')->onDelete('cascade');    
            $table->foreign('invoice_nobukti', 'notakreditdetail_invoiceheader_invoice_nobukti_foreign')->references('nobukti')->on('invoiceheader');    
            $table->foreign('coaadjust', 'notakreditdetail_akunpusat_coalebihbayar_foreign')->references('coa')->on('akunpusat');    


        });
        DB::statement("ALTER TABLE notakreditdetail NOCHECK CONSTRAINT notakreditdetail_invoiceheader_invoice_nobukti_foreign");
        DB::statement("ALTER TABLE notakreditdetail NOCHECK CONSTRAINT notakreditdetail_akunpusat_coalebihbayar_foreign");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notakreditdetail');
    }
}
