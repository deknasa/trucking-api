<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderantruckingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orderantrucking', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->date('tglbukti')->default('1900/1/1');
            $table->unsignedBigInteger('container_id')->default(0);
            $table->unsignedBigInteger('agen_id')->default(0);
            $table->unsignedBigInteger('jenisorder_id')->default(0);
            $table->unsignedBigInteger('pelanggan_id')->default(0);
            $table->unsignedBigInteger('tarif_id')->default(0);
            $table->double('nominal',15,2)->default(0);
            $table->string('nojobemkl',50)->default('');
            $table->string('nocont',50)->default('');
            $table->string('noseal',50)->default('');
            $table->string('nojobemkl2',50)->default('');
            $table->string('nocont2',50)->default('');
            $table->string('noseal2',50)->default('');
            $table->string('modifiedby',50)->default('');
            $table->integer('statuslangsir')->length(11)->default('');
            $table->integer('statusperalihan')->length(11)->default('');
            $table->timestamps();

            $table->foreign('container_id')->references('id')->on('container');
            $table->foreign('agen_id')->references('id')->on('agen');
            $table->foreign('jenisorder_id')->references('id')->on('jenisorder');
            $table->foreign('pelanggan_id')->references('id')->on('pelanggan');
            $table->foreign('tarif_id')->references('id')->on('tarif');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orderantrucking');
    }
}
