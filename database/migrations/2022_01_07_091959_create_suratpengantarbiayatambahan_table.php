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
        Schema::create('suratpengantarbiayatambahan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('suratpengantar_id')->default('0');            
            $table->longText('keteranganbiaya')->default('');            
            $table->decimal('nominal',15,2)->default('0');
            $table->string('modifiedby',50)->default('');            
            $table->timestamps();

            $table->foreign('suratpengantar_id')->references('id')->on('suratpengantar')->onDelete('cascade');             
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
