<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTradosupirmilikmandorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tradosupirmilikmandor', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mandor_id')->nullable();            
            $table->unsignedBigInteger('supir_id')->nullable();            
            $table->unsignedBigInteger('trado_id')->nullable(); 
            $table->string('modifiedby', 50)->nullable();                          
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
        Schema::dropIfExists('tradosupirmilikmandor');
    }
}
