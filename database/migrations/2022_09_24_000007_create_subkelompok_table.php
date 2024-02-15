<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateSubkelompokTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('subkelompok');

        Schema::create('subkelompok', function (Blueprint $table) {
            $table->id();
            $table->string('kodesubkelompok',50)->nullable();
            $table->longText('keterangan')->nullable();
            $table->unsignedBigInteger('kelompok_id')->nullable();
            $table->integer('statusaktif')->length(11)->nullable();
            $table->longText('info')->nullable();
            $table->longText('modifiedby',50)->nullable();
            $table->unsignedBigInteger('tas_id')->nullable();
            $table->timestamps();

            $table->foreign('kelompok_id', 'subkelompok_kelompok_kelompok_id_foreign')->references('id')->on('kelompok');
        });

        DB::statement("ALTER TABLE subkelompok NOCHECK CONSTRAINT subkelompok_kelompok_kelompok_id_foreign");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subkelompok');
    }
}
