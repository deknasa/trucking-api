<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateUpahritasiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::dropIfExists('upahritasi');

        Schema::create('upahritasi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->default('0');
            $table->unsignedBigInteger('tarif_id')->default('0');
            $table->unsignedBigInteger('kotadari_id')->default('0');
            $table->unsignedBigInteger('kotasampai_id')->default('0');
            $table->double('jarak',15,2)->default('0');
            $table->unsignedBigInteger('zona_id')->default('0');
            $table->integer('statusaktif')->length(11)->default('0');
            $table->date('tglmulaiberlaku')->default('1900/1/1');
            $table->string('modifiedby',50)->Default('');            
            $table->timestamps();


            $table->foreign('kotadari_id', 'upahritasi_kota_kotadari_id_foreign')->references('id')->on('kota');
            $table->foreign('kotasampai_id', 'upahritasi_kota_kotasampai_id_foreign')->references('id')->on('kota');
            $table->foreign('zona_id', 'upahritasi_kota_zona_id_foreign')->references('id')->on('zona');
        });

        DB::statement("ALTER TABLE upahritasi NOCHECK CONSTRAINT upahritasi_kota_kotadari_id_foreign");
        DB::statement("ALTER TABLE upahritasi NOCHECK CONSTRAINT upahritasi_kota_kotasampai_id_foreign");
        DB::statement("ALTER TABLE upahritasi NOCHECK CONSTRAINT upahritasi_kota_zona_id_foreign");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('upahritasi');
    }
}
