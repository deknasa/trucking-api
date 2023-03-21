<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePengeluaranstokdetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('pengeluaranstokdetail');
        
        Schema::create('pengeluaranstokdetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pengeluaranstokheader_id');
            $table->string('nobukti',50)->nullable();
            $table->unsignedBigInteger('stok_id');
            $table->double('qty', 15,2)->nullable();
            $table->double('harga', 15,2)->nullable();
            $table->string('persentasediscount', 50)->nullable();
            $table->double('nominaldiscount', 15,2)->nullable();
            $table->double('total', 15,2)->nullable();
            $table->longText('keterangan')->nullable();
            $table->unsignedBigInteger('vulkanisirke')->nullable();
            $table->string('modifiedby',50)->nullable();            
            $table->timestamps();

                      
            $table->foreign('pengeluaranstokheader_id', 'pengeluaranstokdetail_pengeluaranstokheader_pengeluaranstokheader_id_foreign')->references('id')->on('pengeluaranstokheader')->onDelete('cascade');  
            $table->foreign('stok_id', 'pengeluaranstokdetail_stok_stok_id_foreign')->references('id')->on('stok');
        });

        DB::statement("ALTER TABLE pengeluaranstokdetail NOCHECK CONSTRAINT pengeluaranstokdetail_stok_stok_id_foreign");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pengeluaranstokdetail');
    }
}
