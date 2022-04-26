<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterInvoiceextraheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoiceextraheader', function (Blueprint $table) {
            $table->unsignedBigInteger('agen_id')->default(0);

            $table->foreign('agen_id', 'invoiceextraheader_agen_id_foreign')->references('id')->on('agen');



       });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
