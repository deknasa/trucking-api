<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSaldoreminderpergantianTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('saldoreminderpergantian', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trado_id')->nullable();     
            $table->string('nopol',100)->nullable();        
            $table->string('statusreminder',100)->nullable();        
            $table->date('tglawal')->nullable();        
            $table->date('tglsampai')->nullable();        
            $table->double('jarak',15,2)->nullable();     
            $table->longText('info')->nullable();         
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
        Schema::dropIfExists('saldoreminderpergantian');
    }
}
