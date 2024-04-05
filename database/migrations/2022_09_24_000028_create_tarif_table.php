<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateTarifTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::dropIfExists('tarif');

        Schema::create('tarif', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('upahsupir_id')->nullable();
            $table->string('tujuan',200)->nullable();
            $table->longText('penyesuaian')->nullable();
            $table->integer('statusaktif')->length(11)->nullable();
            $table->integer('statussistemton')->length(11)->nullable();
            $table->unsignedBigInteger('kota_id')->nullable();
            $table->unsignedBigInteger('zona_id')->nullable();
            $table->unsignedBigInteger('jenisorder_id')->nullable();
            $table->date('tglmulaiberlaku')->nullable();
            $table->integer('statuspenyesuaianharga')->length(11)->nullable();
            $table->integer('statuspostingtnl')->length(11)->nullable();
            $table->longText('keterangan')->nullable();
            $table->unsignedBigInteger('tas_id')->nullable();
            $table->longText('info')->nullable();
            $table->string('editing_by',50)->nullable();            
            $table->dateTime('editing_at')->nullable();              
            $table->string('modifiedby',50)->nullable();
            $table->timestamps();

            $table->foreign('kota_id', 'tarif_kota_kota_id_foreign')->references('id')->on('kota');
            $table->foreign('zona_id', 'tarif_zona_zona_id_foreign')->references('id')->on('zona');
        });

        DB::statement("ALTER TABLE tarif NOCHECK CONSTRAINT tarif_kota_kota_id_foreign");
        DB::statement("ALTER TABLE tarif NOCHECK CONSTRAINT tarif_zona_zona_id_foreign");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tarif');
    }
}
