<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateSaldojobemklTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('saldojobemkl', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->date('tglbukti')->nullable();
            $table->unsignedBigInteger('shipper_id')->nullable();
            $table->unsignedBigInteger('tujuan_id')->nullable();
            $table->unsignedBigInteger('container_id')->nullable();
            $table->unsignedBigInteger('jenisorder_id')->nullable();
            $table->unsignedBigInteger('marketing_id')->nullable();
            $table->double('nominal', 15,2)->nullable();
            $table->longtext('kapal')->nullable();
            $table->longtext('destination')->nullable();
            $table->longtext('lokasibongkarmuat')->nullable();
            $table->double('nilaiawal',15,2)->nullable();
            $table->string('nocont',50)->nullable();
            $table->string('noseal',50)->nullable();
            $table->timestamps();

            $table->foreign('container_id', 'saldojobemkl_container_container_id_foreign')->references('id')->on('container');
            $table->foreign('tujuan_id', 'saldojobemkl_tujuan_tujuan_id_foreign')->references('id')->on('tujuan');
            $table->foreign('jenisorder_id', 'saldojobemkl_jenisorder_jenisorder_id_foreign')->references('id')->on('jenisorder');
            $table->foreign('shipper_id', 'saldojobemkl_pelanggan_pelanggan_id_foreign')->references('id')->on('pelanggan');
            $table->foreign('marketing_id', 'saldojobemkl_marketing_marketing_id_foreign')->references('id')->on('marketing');

        });

        DB::statement("ALTER TABLE saldojobemkl NOCHECK CONSTRAINT saldojobemkl_container_container_id_foreign");
        DB::statement("ALTER TABLE saldojobemkl NOCHECK CONSTRAINT saldojobemkl_tujuan_tujuan_id_foreign");
        DB::statement("ALTER TABLE saldojobemkl NOCHECK CONSTRAINT saldojobemkl_jenisorder_jenisorder_id_foreign");
        DB::statement("ALTER TABLE saldojobemkl NOCHECK CONSTRAINT saldojobemkl_pelanggan_pelanggan_id_foreign");
        DB::statement("ALTER TABLE saldojobemkl NOCHECK CONSTRAINT saldojobemkl_marketing_marketing_id_foreign");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('saldojobemkl');
    }
}
