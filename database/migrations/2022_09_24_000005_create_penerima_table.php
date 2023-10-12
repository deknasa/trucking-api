<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePenerimaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::dropIfExists('penerima');
        
        Schema::create('penerima', function (Blueprint $table) {
            $table->id();
            $table->string('namapenerima',200)->nullable();
            $table->string('npwp',50)->nullable();
            $table->string('noktp',50)->nullable();
            $table->integer('statusaktif')->length(11)->nullable();
            $table->integer('statuskaryawan')->length(11)->nullable();
            $table->longText('info')->nullable();
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
        Schema::dropIfExists('penerima');
    }
}
