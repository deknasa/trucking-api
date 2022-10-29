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
            $table->date('tglbukti')->default('1900/1/1');
            $table->double('nominal',15,2)->default('0');
            $table->longText('keterangan')->default('');
            $table->date('tglterima')->default('1900/1/1');
            $table->date('tgljatuhtempo')->default('1900/1/1');
            $table->unsignedBigInteger('agen_id')->default('0');
            $table->unsignedBigInteger('jenisorder_id')->default('0');
            $table->unsignedBigInteger('cabang_id')->default('0');
            $table->string('piutang_nobukti', 50)->default('');
            $table->integer('statusapproval')->length(11)->default('0');
            $table->string('userapproval', 50)->default('');
            $table->dateTime('tglapproval')->default('1900/1/1');
            $table->date('tgldari')->default('1900/1/1');
            $table->date('tglsampai')->default('1900/1/1');
            $table->unsignedBigInteger('statusformat')->default(0);
            $table->string('modifiedby', 50)->default('');
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
