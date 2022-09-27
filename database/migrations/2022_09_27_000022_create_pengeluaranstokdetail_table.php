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
            $table->string('nobukti',50)->default('');
            $table->unsignedBigInteger('stok_id');
            $table->integer('conv1')->length(11)->default('0');
            $table->integer('conv2')->length(11)->default('0');
            $table->double('harga', 15,2)->default(0);
            $table->string('persentasediscount', 50)->default('');
            $table->double('nominaldiscount', 15,2)->default(0);
            $table->double('total', 15,2)->default(0);
            $table->longText('keterangan')->default('');
            $table->unsignedBigInteger('vulkanisirke')->default(0);
            $table->string('modifiedby',50)->default('');            
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
