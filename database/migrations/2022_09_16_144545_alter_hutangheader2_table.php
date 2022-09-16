<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterHutangheader2Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hutangheader', function (Blueprint $table) {
            $table->unsignedBigInteger('pelanggan_id')->default(0);
            $table->unsignedBigInteger('statusformat')->default(0);

            $table->foreign('pelanggan_id')->references('id')->on('pelanggan');
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
