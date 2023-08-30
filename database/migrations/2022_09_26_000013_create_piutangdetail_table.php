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

        Schema::dropIfExists('piutangdetail');

        Schema::create('piutangdetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('piutang_id')->nullable();
            $table->string('nobukti',50)->nullable();
            $table->double('nominal',15,2)->nullable();
            $table->longText('keterangan')->nullable();
            $table->string('invoice_nobukti',50)->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby',50)->nullable();
            $table->timestamps();

            $table->foreign('piutang_id', 'piutangdetail_piutangheader_piutang_id_foreign')->references('id')->on('piutangheader')->onDelete('cascade');    
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
