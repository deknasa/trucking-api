<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterPerbaikanstokheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('perbaikanstokheader', function (Blueprint $table) {
            $table->string('deliveryorder_nobukti',50)->default('');

            $table->foreign('deliveryorder_nobukti', 'perbaikanstokheader_deliveryorder_nobukti_foreign')->references('nobukti')->on('deliveryorderheader');
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
