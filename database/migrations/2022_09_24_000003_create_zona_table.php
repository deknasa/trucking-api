<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateZonaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('zona');
        
        Schema::create('zona', function (Blueprint $table) {
            $table->id();
            $table->longText('zona')->nullable();
            $table->longText('keterangan')->nullable();
            $table->string('modifiedby',50)->nullable();
            $table->integer('statusaktif')->length(11)->nullable();
            $table->timestamps();        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('zona');
    }
}
