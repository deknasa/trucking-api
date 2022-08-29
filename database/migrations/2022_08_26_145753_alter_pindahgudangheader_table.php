<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterPindahgudangheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pindahgudangstokheader', function (Blueprint $table) {
            $table->unsignedBigInteger('gudangke_id')->default(0);

            $table->foreign('gudangke_id', 'pindahgudangstokheader_gudang_gudangke_foreign')->references('id')->on('gudang');


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
