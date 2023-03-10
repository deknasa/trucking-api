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
            $table->string('kodekota',1000)->default('');
            $table->longText('keterangan')->default('');
            $table->unsignedBigInteger('zona_id')->default('0');
            $table->integer('statusaktif')->length(11)->default('0');
            $table->string('modifiedby',50)->default('');
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
