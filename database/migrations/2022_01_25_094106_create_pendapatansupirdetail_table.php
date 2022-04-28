<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePendapatansupirdetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pendapatansupirdetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pendapatansupir_id')->default('0');
            $table->string('nobukti',50)->default('');
            $table->unsignedBigInteger('supir_id')->default('0');
            $table->double('nominal',15,2)->default('0');
            $table->longText('keterangan')->default('');
            $table->string('modifiedby',50)->default('');            
            $table->timestamps();

            $table->foreign('pendapatansupir_id')->references('id')->on('pendapatansupirheader')->onDelete('cascade');            
            $table->foreign('supir_id')->references('id')->on('supir');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pendapatansupirdetail');
    }
}
