<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePengeluaranstokdetaillamaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pengeluaranstokdetaillama', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pengeluaranstokheader_id');
            $table->string('nobukti',50)->nullable();
            $table->string('stok',1000)->nullable();
            $table->unsignedBigInteger('stok_id');
            $table->double('qty', 15,2)->nullable();
            $table->double('harga', 15,2)->nullable();
            $table->double('selisihhargafifo', 15,2)->nullable();
            $table->double('persentasediscount', 15,2)->nullable();
            $table->double('nominaldiscount', 15,2)->nullable();
            $table->double('total', 15,2)->nullable();
            $table->longText('keterangan')->nullable();
            $table->unsignedBigInteger('vulkanisirke')->nullable();
            $table->integer('statusservicerutin')->length(11)->nullable();
            $table->integer('statusoli')->length(11)->nullable();
            $table->integer('statusban')->length(11)->nullable();
            $table->string('pengeluaranstok_nobukti',50)->nullable();
            $table->integer('jumlahhariaki')->length(11)->nullable();
            $table->longText('info')->nullable();            
            $table->string('modifiedby',50)->nullable();                
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
        Schema::dropIfExists('pengeluaranstokdetaillama');
    }
}
