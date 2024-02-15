<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatecabangTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('cabang');
        
        Schema::create('cabang', function (Blueprint $table) {
            $table->id();
            $table->string('kodecabang', 300)->nullable();
            $table->string('namacabang', 300)->nullable();
            $table->integer('statusaktif')->length(11)->nullable();
            $table->integer('statuskoneksi')->length(11)->nullable();
            $table->unsignedBigInteger('tas_id')->nullable();
            $table->longText('memo')->nullable();
            $table->longText('info')->nullable();
            $table->longText('judullaporan')->nullable();
            $table->string('modifiedby', 30)->nullable();
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
        Schema::dropIfExists('cabang');
    }
}
