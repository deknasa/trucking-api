<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateHistoryorderantruckingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('historyorderantrucking', function (Blueprint $table) {
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
            $table->string('nojobemkllama',50)->nullable();
            $table->string('nocontlama',50)->nullable();
            $table->string('noseallama',50)->nullable();
            $table->string('nojobemkllama2',50)->nullable();
            $table->string('nocontlama2',50)->nullable();
            $table->string('noseallama2',50)->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby',50)->nullable();
         

            $table->timestamps();


            $table->foreign('container_id', 'historyorderantrucking_container_container_id_foreign')->references('id')->on('container');
            $table->foreign('agen_id', 'historyorderantrucking_agen_agen_id_foreign')->references('id')->on('agen');
            $table->foreign('jenisorder_id', 'historyorderantrucking_jenisorder_jenisorder_id_foreign')->references('id')->on('jenisorder');
            $table->foreign('pelanggan_id', 'historyorderantrucking_pelanggan_pelanggan_id_foreign')->references('id')->on('pelanggan');
            $table->foreign('tarif_id', 'historyorderantrucking_tarif_tarif_id_foreign')->references('id')->on('tarif');

        });

        DB::statement("ALTER TABLE historyorderantrucking NOCHECK CONSTRAINT historyorderantrucking_container_container_id_foreign");
        DB::statement("ALTER TABLE historyorderantrucking NOCHECK CONSTRAINT historyorderantrucking_agen_agen_id_foreign");
        DB::statement("ALTER TABLE historyorderantrucking NOCHECK CONSTRAINT historyorderantrucking_jenisorder_jenisorder_id_foreign");
        DB::statement("ALTER TABLE historyorderantrucking NOCHECK CONSTRAINT historyorderantrucking_pelanggan_pelanggan_id_foreign");
        DB::statement("ALTER TABLE historyorderantrucking NOCHECK CONSTRAINT historyorderantrucking_tarif_tarif_id_foreign");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('historyorderantrucking');
    }
}
