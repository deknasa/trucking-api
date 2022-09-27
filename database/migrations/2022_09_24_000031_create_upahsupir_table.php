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
            $table->unsignedBigInteger('kotadari_id')->default('0');
            $table->unsignedBigInteger('kotasampai_id')->default('0');
            $table->double('jarak',15,2)->default('0');
            $table->unsignedBigInteger('zona_id')->default('0');
            $table->integer('statusaktif')->length(11)->default('0');
            $table->date('tglmulaiberlaku')->default('1900/1/1');
            $table->date('tglakhirberlaku')->default('1900/1/1');
            $table->integer('statusluarkota')->length(11)->default('0');
            $table->string('modifiedby',50)->Default('');            
            $table->timestamps();

            $table->foreign('kotadari_id', 'upahsupir_kota_kotadari_id_foreign')->references('id')->on('kota');
            $table->foreign('kotasampai_id', 'upahsupir_kota_kotasampai_id_foreign')->references('id')->on('kota');
            $table->foreign('zona_id', 'upahsupir_kota_zona_id_foreign')->references('id')->on('zona');

        });

        DB::statement("ALTER TABLE upahsupir NOCHECK CONSTRAINT upahsupir_kota_kotadari_id_foreign");
        DB::statement("ALTER TABLE upahsupir NOCHECK CONSTRAINT upahsupir_kota_kotasampai_id_foreign");
        DB::statement("ALTER TABLE upahsupir NOCHECK CONSTRAINT upahsupir_kota_zona_id_foreign");
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
