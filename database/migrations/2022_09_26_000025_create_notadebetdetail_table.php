<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateNotadebetdetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('notadebetdetail');
        
        Schema::create('notadebetdetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('notadebet_id')->nullable();
            $table->string('nobukti',50)->nullable();
            $table->date('tglterima')->nullable();
            $table->string('invoice_nobukti',50)->nullable();
            $table->double('nominal',15,2)->nullable();
            $table->double('nominalbayar',15,2)->nullable();
            $table->double('lebihbayar',15,2)->nullable();
            $table->longText('keterangan')->nullable();
            $table->string('coalebihbayar',50)->nullable();
            $table->longText('info')->nullable();            
            $table->string('modifiedby',50)->nullable();
            $table->timestamps();


            $table->foreign('notadebet_id', 'notadebetdetail_notadebetheader_notadebet_id_foreign')->references('id')->on('notadebetheader')->onDelete('cascade');    
            $table->foreign('invoice_nobukti', 'notadebetdetail_invoiceheader_invoice_nobukti_foreign')->references('nobukti')->on('invoiceheader');    
            $table->foreign('coalebihbayar', 'notadebetdetail_akunpusat_coalebihbayar_foreign')->references('coa')->on('akunpusat');    



        });
        DB::statement("ALTER TABLE notadebetdetail NOCHECK CONSTRAINT notadebetdetail_invoiceheader_invoice_nobukti_foreign");
        DB::statement("ALTER TABLE notadebetdetail NOCHECK CONSTRAINT notadebetdetail_akunpusat_coalebihbayar_foreign");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notadebetdetail');
    }
}
