<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSatuanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::dropIfExists('satuan');
        
        Schema::create('satuan', function (Blueprint $table) {
            $table->id();
            $table->string('satuan',50)->nullable();
            $table->integer('statusaktif')->length(11)->nullable();
            $table->longText('info')->nullable();
            $table->unsignedBigInteger('tas_id')->nullable();
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
        Schema::dropIfExists('satuan');
    }
}
