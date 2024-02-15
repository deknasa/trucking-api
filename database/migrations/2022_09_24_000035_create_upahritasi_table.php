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
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('tarif_id')->nullable();
            $table->unsignedBigInteger('kotadari_id')->nullable();
            $table->unsignedBigInteger('kotasampai_id')->nullable();
            $table->double('nominalsupir',15,2)->nullable();
            $table->double('jarak',15,2)->nullable();
            $table->unsignedBigInteger('zona_id')->nullable();
            $table->integer('statusaktif')->length(11)->nullable();
            $table->date('tglmulaiberlaku')->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby',50)->nullable();            
            $table->string('editing_by',50)->nullable();            
            $table->dateTime('editing_at')->nullable();            
            $table->unsignedBigInteger('tas_id')->nullable();
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
