<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateErrorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('error', function (Blueprint $table) {
            $table->id();
            $table->string('kodeerror',50)->nullable();            
            $table->longText('keterangan')->nullable();            
            $table->longText('info')->nullable();
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
        Schema::dropIfExists('error');
    }
}
