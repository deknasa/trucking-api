<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSuratpengantarbiayatambahanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::dropIfExists('suratpengantarbiayatambahan');
        
        Schema::create('suratpengantarbiayatambahan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('suratpengantar_id')->nullable();            
            $table->longText('keteranganbiaya')->nullable();            
            $table->decimal('nominal',15,2)->nullable();
            $table->decimal('nominaltagih',15,2)->nullable();
            $table->string('modifiedby',50)->nullable();            
            $table->timestamps();

            $table->foreign('suratpengantar_id', 'suratpengantarbiayatambahan_suratpengantar_suratpengantar_id_foreign')->references('id')->on('suratpengantar')->onDelete('cascade');    

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('suratpengantarbiayatambahan');
    }
}
