<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSaldogajisupirsuratpengantarbiayatambahanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('saldogajisupirsuratpengantarbiayatambahan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('suratpengantar_id')->nullable();            
            $table->longText('keteranganbiaya')->nullable();            
            $table->decimal('nominal',15,2)->nullable();
            $table->decimal('nominaltagih',15,2)->nullable();
            $table->integer('statusapproval')->length(11)->nullable();
            $table->string('userapproval', 50)->nullable();
            $table->dateTime('tglapproval')->nullable();            
            $table->longText('info')->nullable();
            $table->string('modifiedby',50)->nullable();              
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
        Schema::dropIfExists('saldogajisupirsuratpengantarbiayatambahan');
    }
}
