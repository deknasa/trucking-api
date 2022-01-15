<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKomisisupirdepositoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('komisisupirdeposito', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('komisisupir_id')->default('0');
            $table->string('nobukti',50)->default('');
            $table->date('tgl')->default('1900/1/1');
            $table->string('keterangan',250)->default('');
            $table->unsignedBigInteger('supir_id')->default('0');
            $table->double('nominal',15,2)->default('0');
            $table->string('modifiedby',50)->default('');
            $table->timestamps();

            $table->foreign('komisisupir_id')->references('id')->on('komisisupirheader')->onDelete('cascade');            
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('komisisupirdeposito');
    }
}
