<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePenerimaanstokdetaillamaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('penerimaanstokdetaillama', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('penerimaanstokheader_id');
            $table->string('nobukti', 50)->nullable();
            $table->unsignedBigInteger('stok_id');
            $table->string('stok', 1000)->nullable();
            $table->double('qty', 15, 2)->nullable();
            $table->double('harga', 15, 2)->nullable();
            $table->double('persentasediscount', 15, 2)->nullable();
            $table->double('nominaldiscount', 15, 2)->nullable();
            $table->double('total', 15, 2)->nullable();
            $table->longText('keterangan')->nullable();
            $table->unsignedBigInteger('vulkanisirke')->nullable();
            $table->string('penerimaanstok_nobukti', 50)->nullable();
            $table->double('qtykeluar', 15, 2)->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('penerimaanstokdetaillama');
    }
}
