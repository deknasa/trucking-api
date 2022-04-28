<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePiutangheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('piutangheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->date('tglbukti')->default('1900/1/1');
            $table->longText('keterangan')->default('');
            $table->string('postingdari',150)->default('');
            $table->double('nominal',15,2)->default('0');
            $table->string('invoice_nobukti',50)->default('');
            $table->string('modifiedby',50)->default('');
            $table->timestamps();

            
            $table->foreign('invoice_nobukti')->references('nobukti')->on('invoiceheader');


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('piutangheader');
    }
}
