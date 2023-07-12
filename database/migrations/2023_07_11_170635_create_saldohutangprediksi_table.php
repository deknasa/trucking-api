<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSaldohutangprediksiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('saldohutangprediksi', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',200)->nullable();
            $table->longText('keterangan')->nullable();
            $table->double('nominal',15,2)->nullable();             
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
        Schema::dropIfExists('saldohutangprediksi');
    }
}
