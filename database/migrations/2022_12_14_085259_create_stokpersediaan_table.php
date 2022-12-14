<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateStokpersediaanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('stokpersediaan');

        Schema::create('stokpersediaan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stok_id')->default('0');
            $table->unsignedBigInteger('gudang_id')->default('0');
            $table->unsignedBigInteger('trado_id')->default('0');
            $table->unsignedBigInteger('gandengan_id')->default('0');
            $table->double('qty',15,2)->default('0');
            $table->string('modifiedby',50)->default('');            
            $table->timestamps();

            $table->foreign('stok_id', 'stokpersediaan_stok_stok_id_foreign')->references('id')->on('stok')->onDelete('cascade');       
            $table->foreign('gudang_id', 'stokpersediaan_gudang_gudang_id_foreign')->references('id')->on('gudang');
            $table->foreign('trado_id', 'stokpersediaan_trado_trado_id_foreign')->references('id')->on('trado');
            $table->foreign('gandengan_id', 'stokpersediaan_gandengan_gandengan_id_foreign')->references('id')->on('gandengan');

        });

        DB::statement("ALTER TABLE stokpersediaan NOCHECK CONSTRAINT stokpersediaan_gudang_gudang_id_foreign");
        DB::statement("ALTER TABLE stokpersediaan NOCHECK CONSTRAINT stokpersediaan_trado_trado_id_foreign");
        DB::statement("ALTER TABLE stokpersediaan NOCHECK CONSTRAINT stokpersediaan_gandengan_gandengan_id_foreign");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stokpersediaan');
    }
}
