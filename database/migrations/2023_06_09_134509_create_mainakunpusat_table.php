<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMainakunpusatTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mainakunpusat', function (Blueprint $table) {
            $table->id();
            $table->string('coa', 50)->unique();
            $table->longText('keterangancoa')->nullable();
            $table->unsignedBigInteger('type_id')->nullable();
            $table->string('type', 50)->nullable();
            $table->integer('level')->length(11)->nullable();
            $table->integer('statusaktif')->length(11)->nullable();
            $table->string('parent', 255)->nullable();
            $table->integer('statuscoa')->length(11)->nullable();
            $table->integer('statusaccountpayable')->length(11)->nullable();
            $table->integer('statusparent')->length(11)->nullable();            
            $table->unsignedBigInteger('akuntansi_id')->nullable();
            $table->integer('statusneraca')->length(11)->nullable();
            $table->integer('statuslabarugi')->length(11)->nullable();
            $table->string('coamain', 30)->nullable();
            $table->unsignedBigInteger('tas_id')->nullable();
            $table->longText('info')->nullable();
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
        Schema::dropIfExists('mainakunpusat');
    }
}
