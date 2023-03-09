<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateInvoicechargegandenganheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoicechargegandenganheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti', 50)->unique();
            $table->date('tglbukti')->default('1900/1/1');
            $table->unsignedBigInteger('agen_id')->default('0');
            $table->double('nominal')->default('0');
            $table->integer('statusapproval')->length(11)->default('0');
            $table->string('userapproval', 50)->default('');
            $table->dateTime('tglapproval')->default('1900/1/1');
            $table->unsignedBigInteger('statusformat')->default(0);
            $table->integer('statuscetak')->Length(11)->default('0');
            $table->string('userbukacetak',50)->default('');
            $table->date('tglbukacetak')->default('1900/1/1');
            $table->integer('jumlahcetak')->Length(11)->default('0');
            $table->string('modifiedby', 50)->default('');            
            $table->timestamps();

            $table->foreign('agen_id', 'invoicechargegandenganheader_agen_agen_id_foreign')->references('id')->on('agen');

        });

        DB::statement("ALTER TABLE invoicechargegandenganheader NOCHECK CONSTRAINT invoicechargegandenganheader_agen_agen_id_foreign");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoicechargegandenganheader');
    }
}
