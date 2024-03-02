<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateOrderantruckingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('orderantrucking');

        Schema::create('orderantrucking', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->date('tglbukti')->nullable();
            $table->unsignedBigInteger('container_id')->nullable();
            $table->unsignedBigInteger('agen_id')->nullable();
            $table->unsignedBigInteger('jenisorder_id')->nullable();
            $table->unsignedBigInteger('jenisorderemkl_id')->nullable();
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
            $table->dateTime('tglbatasbukatrip')->nullable();
            $table->integer('statusapprovaledit')->Length(11)->nullable();
            $table->date('tglapprovaledit')->nullable();
            $table->string('userapprovaledit',50)->nullable();
            $table->dateTime('tglbataseditorderantrucking')->nullable();
            $table->integer('statusapprovaltanpajob')->Length(11)->nullable();
            $table->date('tglapprovaltanpajob')->nullable();
            $table->string('userapprovaltanpajob',50)->nullable();
            $table->dateTime('tglbatastanpajoborderantrucking')->nullable();            
            $table->unsignedBigInteger('statusformat')->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby',50)->nullable();
            $table->string('editing_by',50)->nullable();            
            $table->dateTime('editing_at')->nullable();            

            $table->timestamps();


            $table->foreign('container_id', 'orderantrucking_container_container_id_foreign')->references('id')->on('container');
            $table->foreign('agen_id', 'orderantrucking_agen_agen_id_foreign')->references('id')->on('agen');
            $table->foreign('jenisorder_id', 'orderantrucking_jenisorder_jenisorder_id_foreign')->references('id')->on('jenisorder');
            $table->foreign('pelanggan_id', 'orderantrucking_pelanggan_pelanggan_id_foreign')->references('id')->on('pelanggan');
            $table->foreign('tarif_id', 'orderantrucking_tarif_tarif_id_foreign')->references('id')->on('tarif');


        });

        DB::statement("ALTER TABLE orderantrucking NOCHECK CONSTRAINT orderantrucking_container_container_id_foreign");
        DB::statement("ALTER TABLE orderantrucking NOCHECK CONSTRAINT orderantrucking_agen_agen_id_foreign");
        DB::statement("ALTER TABLE orderantrucking NOCHECK CONSTRAINT orderantrucking_jenisorder_jenisorder_id_foreign");
        DB::statement("ALTER TABLE orderantrucking NOCHECK CONSTRAINT orderantrucking_pelanggan_pelanggan_id_foreign");
        DB::statement("ALTER TABLE orderantrucking NOCHECK CONSTRAINT orderantrucking_tarif_tarif_id_foreign");

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
