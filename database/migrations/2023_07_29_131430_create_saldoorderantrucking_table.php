<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSaldoorderantruckingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('saldoorderantrucking', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->date('tglbukti')->nullable();
            $table->unsignedBigInteger('container_id')->nullable();
            $table->unsignedBigInteger('agen_id')->nullable();
            $table->unsignedBigInteger('jenisorder_id')->nullable();
            $table->unsignedBigInteger('pelanggan_id')->nullable();
            $table->unsignedBigInteger('tarif_id')->nullable();
            $table->double('nominal',15,2)->nullable();
            $table->string('nojobemkl',50)->nullable();
            $table->string('nocont',50)->nullable();
            $table->string('noseal',50)->nullable();
            $table->string('nojobemkl2',50)->nullable();
            $table->string('nocont2',50)->nullable();
            $table->string('noseal2',50)->nullable();
            $table->integer('statuslangsir')->length(11)->nullable();
            $table->integer('statusperalihan')->length(11)->nullable();
            $table->string('jobtruckingasal',500)->nullable();
            $table->integer('statusapprovalnonchargegandengan')->Length(11)->nullable();
            $table->string('userapprovalnonchargegandengan',50)->nullable();
            $table->date('tglapprovalnonchargegandengan')->nullable();            
            $table->integer('statusapprovalbukatrip')->Length(11)->nullable();
            $table->date('tglapprovalbukatrip')->nullable();
            $table->string('userapprovalbukatrip',50)->nullable();
            $table->unsignedBigInteger('statusformat')->nullable();
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
        Schema::dropIfExists('saldoorderantrucking');
    }
}
