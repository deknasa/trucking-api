<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicelunaskepusatTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('invoicelunaskepusat', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoiceheader_id')->nullable();
            $table->string('nobukti',50)->nullable();
            $table->date('tglbukti')->nullable();
            $table->unsignedBigInteger('agen_id')->nullable();
            $table->double('nominal',15,2)->nullable();
            $table->date('tglbayar')->nullable();
            $table->double('bayar',15,2)->nullable();
            $table->double('sisa',15,2)->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby',50)->nullable();     
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoicelunaskepusat');
    }
}
