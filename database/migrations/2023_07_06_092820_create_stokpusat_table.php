<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStokpusatTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stokpusat', function (Blueprint $table) {
            $table->id();
            $table->Integer('idpusatold')->nullable();
            $table->string('namastok',200)->nullable();
            $table->unsignedBigInteger('kelompok_id')->nullable();
            $table->longText('gambar')->nullable();
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
        Schema::dropIfExists('stokpusat');
    }
}
