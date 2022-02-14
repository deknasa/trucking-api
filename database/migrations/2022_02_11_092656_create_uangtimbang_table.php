<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUangtimbangTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('uangtimbang', function (Blueprint $table) {
            $table->id();
            $table->longText('keterangan')->default('');            
            $table->decimal('nominal',15,2)->default('0');            
            $table->string('modifiedby',50)->default('');            
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
        Schema::dropIfExists('uangtimbang');
    }
}
