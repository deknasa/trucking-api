<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSaldopendapatansupirTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('saldopendapatansupir', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supir_id')->nullable();     
            $table->string('gajisupir_nobukti',100)->nullable();        
            $table->string('suratpengantar_nobukti',100)->nullable();        
            $table->date('suratpengantar_tglbukti')->nullable();        
            $table->longText('dari_id')->nullable();        
            $table->longText('sampai_id')->nullable();        
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
        Schema::dropIfExists('saldopendapatansupir');
    }
}
