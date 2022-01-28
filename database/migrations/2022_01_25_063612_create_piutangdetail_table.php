<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePiutangdetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('piutangdetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('piutang_id')->default('0');
            $table->string('nobukti',50)->default('');
            $table->double('nominal',15,2)->default('0');
            $table->string('noinvoice',50)->default('');
            $table->longText('keterangan')->default('');
            $table->string('invoice_nobukti',50)->default('');
            $table->string('modifiedby',50)->default('');
            $table->timestamps();

            $table->foreign('piutang_id')->references('id')->on('piutangheader')->onDelete('cascade');             

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('piutangdetail');
    }
}
