<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProsesgajisupirdepositoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prosesgajisupirdeposito', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prosesgajisupir_id')->default('0');
            $table->string('nobukti',50)->default('');
            $table->string('deposito_nobukti',50)->default('');
            $table->longText('keterangan')->default('');
            $table->unsignedBigInteger('supir_id')->default('0');
            $table->double('nominal',15,2)->default('0');
            $table->string('modifiedby',50)->default('');
            $table->timestamps();

            $table->foreign('prosesgajisupir_id')->references('id')->on('prosesgajisupirheader')->onDelete('cascade');                         
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
        Schema::dropIfExists('prosesgajisupirdeposito');
    }
}
