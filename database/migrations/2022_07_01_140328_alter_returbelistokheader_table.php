<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterReturbelistokheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('returbelistokheader', function (Blueprint $table) {
            $table->foreign('belistok_nobukti', 'belistokheader_returbelistok_nobukti_foreign')->references('nobukti')->on('belistokheader');
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
