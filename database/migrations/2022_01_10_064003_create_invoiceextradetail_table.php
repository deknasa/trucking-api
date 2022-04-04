<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceextradetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoiceextradetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoiceextra_id')->default(0);
            $table->string('nobukti', 50)->default('');
            $table->date('qty',15,2)->default('0');
            $table->date('hrgsatuan',15,2)->default('0');
            $table->date('total',15,2)->default('0');
            $table->string('persentasedisccount', 50)->default('');
            $table->date('nominaldiscount',15,2)->default('0');
            $table->date('biaya',15,2)->default('0');
            $table->date('nominal',15,2)->default('0');
            $table->longText('keterangan')->default('');
            $table->string('modifiedby', 50)->default('');
            $table->timestamps();

            $table->foreign('invoiceextra_id')->references('id')->on('invoiceextraheader')->onDelete('cascade');            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoiceextradetail');
    }
}
