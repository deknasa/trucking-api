<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGajisupirdepositoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gajisupirdeposito', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('gajisupir_id')->default('0');
            $table->string('nobukti',50)->default('');
            $table->string('nobukti_deposito',50)->default('');
            $table->date('tgl')->default('1900/1/1');
            $table->longText('keterangan')->default('');
            $table->unsignedBigInteger('supir_id')->default('0');
            $table->double('nominal',15,2)->default('0');
            $table->string('modifiedby',50)->default('');
            $table->timestamps();

            $table->foreign('gajisupir_id')->references('id')->on('gajisupirheader')->onDelete('cascade');                         
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
        Schema::dropIfExists('gajisupirdeposito');
    }
}
