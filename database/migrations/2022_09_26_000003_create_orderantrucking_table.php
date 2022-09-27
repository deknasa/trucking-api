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
            $table->integer('statuslangsir')->length(11)->default('');
            $table->integer('statusperalihan')->length(11)->default('');
            $table->unsignedBigInteger('statusformat')->default(0);
            $table->string('modifiedby',50)->default('');
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
