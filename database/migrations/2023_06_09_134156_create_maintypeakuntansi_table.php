<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMaintypeakuntansiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('maintypeakuntansi', function (Blueprint $table) {
            $table->id();
            $table->string('kodetype', 100)->nullable();
            $table->integer('order')->nullable();            
            $table->longText('keterangantype')->nullable();
            $table->unsignedBigInteger('akuntansi_id')->nullable();
            $table->integer('statusaktif')->length(11)->nullable();
            $table->unsignedBigInteger('tas_id')->nullable();
            $table->longText('info')->nullable();
            $table->string('editing_by',50)->nullable();            
            $table->dateTime('editing_at')->nullable();                
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
        Schema::dropIfExists('maintypeakuntansi');
    }
}
