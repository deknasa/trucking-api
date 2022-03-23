<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterSuratpengantar2Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('suratpengantar', function (Blueprint $table) {
            $table->double('gajiritasikenek',15,2)->default(0);
            $table->double('omset',15,2)->default(0);
            $table->double('discount',15,2)->default(0);
            $table->double('totalomset',15,2)->default(0);

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
