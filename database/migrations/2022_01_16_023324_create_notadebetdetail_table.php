<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotadebetdetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
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

            $table->foreign('notadebet_id')->references('id')->on('notadebetheader')->onDelete('cascade');            
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
        Schema::dropIfExists('notadebetdetail');
    }
}
