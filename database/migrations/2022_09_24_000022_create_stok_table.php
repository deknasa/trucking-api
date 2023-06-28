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
            $table->unsignedBigInteger('jenistrado_id')->nullable();
            $table->unsignedBigInteger('kelompok_id')->nullable();
            $table->unsignedBigInteger('subkelompok_id')->nullable();
            $table->unsignedBigInteger('kategori_id')->nullable();
            $table->unsignedBigInteger('merk_id')->nullable();
            $table->unsignedBigInteger('satuan_id')->nullable();
            $table->string('namastok',200)->nullable();
            $table->integer('statusaktif')->length(11)->nullable();
            $table->integer('statusreuse')->length(11)->nullable();
            $table->integer('statusban')->length(11)->nullable();
            $table->integer('statusservicerutin')->length(11)->nullable();
            $table->double('qtymin',15,2)->nullable();
            $table->double('qtymax',15,2)->nullable();
            $table->double('hargabelimin',15,2)->nullable();
            $table->double('hargabelimax',15,2)->nullable();
            $table->double('vulkanisirawal',15,2)->nullable();
            $table->double('totalvulkanisir',15,2)->nullable();
            $table->longText('keterangan')->nullable();
            $table->longText('gambar')->nullable();
            $table->longText('namaterpusat')->nullable();
            $table->string('modifiedby',50)->nullable();
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
