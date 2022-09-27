<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateStokTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::dropIfExists('stok');
        
        Schema::create('stok', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('jenistrado_id')->default('0');
            $table->unsignedBigInteger('kelompok_id')->default('0');
            $table->unsignedBigInteger('subkelompok_id')->default('0');
            $table->unsignedBigInteger('kategori_id')->default('0');
            $table->unsignedBigInteger('merk_id')->default('0');
            $table->integer('conv1')->length(11)->default('0');
            $table->integer('conv2')->length(11)->default('0');
            $table->string('namastok',200)->default('');
            $table->integer('statusaktif')->length(11)->default('0');
            $table->double('qtymin',15,2)->default('0');
            $table->double('qtymax',15,2)->default('0');
            $table->longText('keterangan')->default('');
            $table->longText('gambar')->default('');
            $table->longText('namaterpusat')->default('');
            $table->string('modifiedby',50)->default('');
            $table->timestamps();

            $table->foreign('jenistrado_id', 'stok_jenistrado_jenistrado_id_foreign')->references('id')->on('jenistrado');
            $table->foreign('kelompok_id', 'stok_kelompok_kelompok_id_foreign')->references('id')->on('kelompok');
            $table->foreign('subkelompok_id', 'stok_subkelompok_subkelompok_id_foreign')->references('id')->on('subkelompok');
            $table->foreign('kategori_id', 'stok_kategori_kategori_id_foreign')->references('id')->on('kategori');
            $table->foreign('merk_id', 'stok_merk_merk_id_foreign')->references('id')->on('merk');

        });

        DB::statement("ALTER TABLE stok NOCHECK CONSTRAINT stok_jenistrado_jenistrado_id_foreign");
        DB::statement("ALTER TABLE stok NOCHECK CONSTRAINT stok_kelompok_kelompok_id_foreign");
        DB::statement("ALTER TABLE stok NOCHECK CONSTRAINT stok_subkelompok_subkelompok_id_foreign");
        DB::statement("ALTER TABLE stok NOCHECK CONSTRAINT stok_kategori_kategori_id_foreign");
        DB::statement("ALTER TABLE stok NOCHECK CONSTRAINT stok_merk_merk_id_foreign");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stok');
    }
}
