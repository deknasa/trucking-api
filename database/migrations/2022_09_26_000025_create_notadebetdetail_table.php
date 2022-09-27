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
            $table->unsignedBigInteger('notadebet_id')->default('0');
            $table->string('nobukti',50)->default('');
            $table->date('tglterima')->default('1900/1/1');
            $table->string('invoice_nobukti',50)->default('');
            $table->double('nominal',15,2)->default('0');
            $table->double('nominalbayar',15,2)->default('0');
            $table->double('lebihbayar',15,2)->default('0');
            $table->longText('keterangan')->default('');
            $table->string('coalebihbayar',50)->default('');
            $table->string('modifiedby',50)->default('');
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
