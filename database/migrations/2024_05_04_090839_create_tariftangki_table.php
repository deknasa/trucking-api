<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTariftangkiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tariftangki', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('upahsupirtangki_id')->nullable();
            $table->string('tujuan',200)->nullable();
            $table->longText('penyesuaian')->nullable();
            $table->integer('statusaktif')->length(11)->nullable();
            $table->unsignedBigInteger('kota_id')->nullable();
            $table->date('tglmulaiberlaku')->nullable();
            $table->integer('statuspenyesuaianharga')->length(11)->nullable();
            $table->integer('statuspostingtnl')->length(11)->nullable();
            $table->longText('keterangan')->nullable();
            $table->unsignedBigInteger('tas_id')->nullable();
            $table->double('nominal',15,2)->nullable();            
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
        Schema::dropIfExists('tariftangki');
    }
}
