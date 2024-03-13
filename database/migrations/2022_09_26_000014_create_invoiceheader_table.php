<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateInvoiceheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('invoiceheader');
        
        Schema::create('invoiceheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti', 50)->unique();
            $table->date('tglbukti')->nullable();
            $table->longText('keterangan')->nullable();            
            $table->double('nominal',15,2)->nullable();
            $table->date('tglterima')->nullable();
            $table->date('tgljatuhtempo')->nullable();
            $table->string('noinvoicepajak', 50)->nullable();
            $table->unsignedBigInteger('agen_id')->nullable();
            $table->unsignedBigInteger('jenisorder_id')->nullable();
            $table->unsignedBigInteger('cabang_id')->nullable();
            $table->string('piutang_nobukti', 50)->nullable();
            $table->integer('statuspilihaninvoice')->length(11)->nullable();
            $table->integer('statusapproval')->length(11)->nullable();
            $table->string('userapproval', 50)->nullable();
            $table->dateTime('tglapproval')->nullable();
            $table->date('tgldari')->nullable();
            $table->date('tglsampai')->nullable();
            $table->unsignedBigInteger('statusformat')->nullable();
            $table->integer('statuscetak')->Length(11)->nullable();
            $table->string('userbukacetak',50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->Length(11)->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->string('editing_by',50)->nullable();            
            $table->dateTime('editing_at')->nullable();            

            $table->timestamps();

            $table->foreign('agen_id', 'invoiceheader_agen_agen_id_foreign')->references('id')->on('agen');
            $table->foreign('jenisorder_id', 'invoiceheader_jenisorder_jenisorder_id_foreign')->references('id')->on('jenisorder');
            $table->foreign('cabang_id', 'invoiceheader_cabang_cabang_id_foreign')->references('id')->on('cabang');
            $table->foreign('piutang_nobukti', 'invoiceheader_piutangheader_piutang_nobukti_foreign')->references('nobukti')->on('piutangheader');

    
        });
        DB::statement("ALTER TABLE invoiceheader NOCHECK CONSTRAINT invoiceheader_agen_agen_id_foreign");
        DB::statement("ALTER TABLE invoiceheader NOCHECK CONSTRAINT invoiceheader_jenisorder_jenisorder_id_foreign");
        DB::statement("ALTER TABLE invoiceheader NOCHECK CONSTRAINT invoiceheader_cabang_cabang_id_foreign");
        DB::statement("ALTER TABLE invoiceheader NOCHECK CONSTRAINT invoiceheader_piutangheader_piutang_nobukti_foreign");


     
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoiceheader');
    }
}
