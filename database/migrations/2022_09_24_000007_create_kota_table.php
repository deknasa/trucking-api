<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateKotaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::dropIfExists('kota');

        Schema::create('kota', function (Blueprint $table) {
            $table->id();
            $table->string('kodekota',1000)->nullable();
            $table->longText('keterangan')->nullable();
            $table->unsignedBigInteger('zona_id')->nullable();
            $table->integer('statusaktif')->length(11)->nullable();
            $table->longText('info')->nullable();
            $table->unsignedBigInteger('tas_id')->nullable();
            $table->string('editing_by',50)->nullable();            
            $table->dateTime('editing_at')->nullable();            

            $table->string('modifiedby',50)->nullable();
            $table->timestamps();

            $table->foreign('zona_id', 'kota_zona_zona_id_foreign')->references('id')->on('zona');
        });

        DB::statement("ALTER TABLE kota NOCHECK CONSTRAINT kota_zona_zona_id_foreign");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kota');
    }
}
