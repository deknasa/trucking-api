<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateBiayaextrasupirdetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('biayaextrasupirdetail', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti', 50)->nullable();            
            $table->unsignedBigInteger('biayaextrasupir_id')->nullable();            
            $table->longText('keteranganbiaya')->nullable();            
            $table->decimal('nominal',15,2)->nullable();
            $table->decimal('nominaltagih',15,2)->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby',50)->nullable();              
            $table->timestamps();

            $table->foreign('biayaextrasupir_id', 'biayaextrasupirdetail_biayaextrasupirheader_biayaextrasupir_id_foreign')->references('id')->on('biayaextrasupirheader')->onDelete('cascade');    

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('biayaextrasupirdetail');
    }
}
