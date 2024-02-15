<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateUpahsupirTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::dropIfExists('upahsupir');

        Schema::create('upahsupir', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('tarif_id')->nullable();
            $table->unsignedBigInteger('tarifmuatan_id')->nullable();
            $table->unsignedBigInteger('tarifbongkaran_id')->nullable();
            $table->unsignedBigInteger('tarifimport_id')->nullable();
            $table->unsignedBigInteger('tarifexport_id')->nullable();
            $table->unsignedBigInteger('kotadari_id')->nullable();
            $table->unsignedBigInteger('kotasampai_id')->nullable();
            $table->unsignedBigInteger('zonadari_id')->nullable();
            $table->unsignedBigInteger('zonasampai_id')->nullable();
            $table->longText('penyesuaian')->nullable();            
            $table->double('jarak',15,2)->nullable();
            $table->double('jarakfullempty',15,2)->nullable();
            $table->unsignedBigInteger('zona_id')->nullable();
            $table->integer('statusaktif')->length(11)->nullable();
            $table->date('tglmulaiberlaku')->nullable();
            $table->integer('statusluarkota')->length(11)->nullable();
            $table->integer('statusupahzona')->length(11)->nullable();
            $table->integer('statussimpankandang')->length(11)->nullable();
            $table->integer('statuspostingtnl')->length(11)->nullable();
            $table->longText('keterangan')->nullable();
            $table->longText('gambar')->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby',50)->nullable();            
            $table->string('editing_by',50)->nullable();            
            $table->dateTime('editing_at')->nullable();         
            $table->unsignedBigInteger('tas_id')->nullable();
            $table->timestamps();

            $table->foreign('kotadari_id', 'upahsupir_kota_kotadari_id_foreign')->references('id')->on('kota');
            $table->foreign('kotasampai_id', 'upahsupir_kota_kotasampai_id_foreign')->references('id')->on('kota');
            $table->foreign('zona_id', 'upahsupir_kota_zona_id_foreign')->references('id')->on('zona');
            $table->foreign('parent_id', 'upahsupir_upahsupir_upahsupir_id_foreign')->references('id')->on('upahsupir');
            $table->foreign('tarif_id', 'upahsupir_tarif_tarif_id_foreign')->references('id')->on('tarif');


        });

        DB::statement("ALTER TABLE upahsupir NOCHECK CONSTRAINT upahsupir_kota_kotadari_id_foreign");
        DB::statement("ALTER TABLE upahsupir NOCHECK CONSTRAINT upahsupir_kota_kotasampai_id_foreign");
        DB::statement("ALTER TABLE upahsupir NOCHECK CONSTRAINT upahsupir_kota_zona_id_foreign");
        DB::statement("ALTER TABLE upahsupir NOCHECK CONSTRAINT upahsupir_upahsupir_upahsupir_id_foreign");
        DB::statement("ALTER TABLE upahsupir NOCHECK CONSTRAINT upahsupir_tarif_tarif_id_foreign");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('upahsupir');
    }
}
