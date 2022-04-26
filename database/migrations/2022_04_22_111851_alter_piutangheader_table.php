<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterPiutangheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('piutangheader', function (Blueprint $table) {
            $table->foreign('invoice_nobukti', 'piutangheader_invoiceextraheader_nobukti_foreign')->references('nobukti')->on('invoiceextraheader');
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
