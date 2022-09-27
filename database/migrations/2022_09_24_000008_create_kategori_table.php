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
            $table->string('kodekategori',50)->default('');
            $table->longText('keterangan')->default('');
            $table->unsignedBigInteger('subkelompok_id')->default('0');
            $table->integer('statusaktif')->length(11)->default('0');
            $table->string('modifiedby',50)->default('');
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
