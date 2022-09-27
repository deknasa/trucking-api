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
            $table->unsignedBigInteger('notakredit_id')->default('0');
            $table->string('nobukti',50)->default('');
            $table->date('tglterima')->default('1900/1/1');
            $table->string('invoice_nobukti',50)->default('');
            $table->double('nominal',15,2)->default('0');
            $table->double('nominalbayar',15,2)->default('0');
            $table->double('penyesuaian',15,2)->default('0');
            $table->longText('keterangan')->default('');
            $table->string('coaadjust',50)->default('');
            $table->string('modifiedby',50)->default('');
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
