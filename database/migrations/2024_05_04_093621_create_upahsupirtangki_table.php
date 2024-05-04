<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateUpahsupirtangkiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('upahsupirtangki', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('tariftangki_id')->nullable();
            $table->unsignedBigInteger('kotadari_id')->nullable();
            $table->unsignedBigInteger('kotasampai_id')->nullable();
            $table->longText('penyesuaian')->nullable();  
            $table->double('jarak',15,2)->nullable();
            $table->integer('statusaktif')->length(11)->nullable();
            $table->date('tglmulaiberlaku')->nullable();
            $table->longText('keterangan')->nullable();
            $table->longText('gambar')->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby',50)->nullable();            
            $table->string('editing_by',50)->nullable();            
            $table->dateTime('editing_at')->nullable();         
            $table->unsignedBigInteger('tas_id')->nullable();            
            $table->timestamps();

            $table->foreign('kotadari_id', 'upahsupirtangki_kota_kotadari_id_foreign')->references('id')->on('kota');
            $table->foreign('kotasampai_id', 'upahsupirtangki_kota_kotasampai_id_foreign')->references('id')->on('kota');
            $table->foreign('parent_id', 'upahsupirtangki_upahsupirtangki_upahsupirtangki_id_foreign')->references('id')->on('upahsupirtangki');
            $table->foreign('tarif_id', 'upahsupirtangki_tarif_tarif_id_foreign')->references('id')->on('tarif');


        });

        DB::statement("ALTER TABLE upahsupirtangki NOCHECK CONSTRAINT upahsupirtangki_kota_kotadari_id_foreign");
        DB::statement("ALTER TABLE upahsupirtangki NOCHECK CONSTRAINT upahsupirtangki_kota_kotasampai_id_foreign");
        DB::statement("ALTER TABLE upahsupirtangki NOCHECK CONSTRAINT upahsupirtangki_upahsupirtangki_upahsupirtangki_id_foreign");
        DB::statement("ALTER TABLE upahsupirtangki NOCHECK CONSTRAINT upahsupirtangki_tarif_tarif_id_foreign");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('upahsupirtangki');
    }
}
