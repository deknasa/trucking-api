<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceemklfifoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoiceemklfifo', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti', 50)->nullable();
            $table->string('jobemkl_nobukti', 50)->nullable();
            $table->string('status', 50)->nullable();
            $table->double('nominal', 15,2)->nullable();
            $table->double('nominalpelunasan', 15,2)->nullable();
            $table->string('coadebet', 50)->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby', 50)->nullable();
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
        Schema::dropIfExists('invoiceemklfifo');
    }
}
