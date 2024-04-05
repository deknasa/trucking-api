<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateKategoriTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::dropIfExists('kategori');

        Schema::create('kategori', function (Blueprint $table) {
            $table->id();
            $table->string('kodekategori',500)->nullable();
            $table->longText('keterangan')->nullable();
            $table->unsignedBigInteger('subkelompok_id')->nullable();
            $table->integer('statusaktif')->length(11)->nullable();
            $table->unsignedBigInteger('tas_id')->nullable();
            $table->string('editing_by',50)->nullable();            
            $table->dateTime('editing_at')->nullable();            

            $table->longText('info')->nullable();
            $table->string('modifiedby',50)->nullable();
            $table->timestamps();

            $table->foreign('subkelompok_id', 'kategori_subkelompok_subkelompok_id_foreign')->references('id')->on('subkelompok');
        });

        DB::statement("ALTER TABLE kategori NOCHECK CONSTRAINT kategori_subkelompok_subkelompok_id_foreign");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kategori');
    }
}
