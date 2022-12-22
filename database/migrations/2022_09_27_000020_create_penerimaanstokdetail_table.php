<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePenerimaanstokdetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('penerimaanstokdetail');

        Schema::create('penerimaanstokdetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('penerimaanstokheader_id');
            $table->string('nobukti',50)->default('');
            $table->unsignedBigInteger('stok_id');
            $table->double('qty', 15,2)->default(0);
            $table->double('harga', 15,2)->default(0);
            $table->string('persentasediscount', 50)->default('');
            $table->double('nominaldiscount', 15,2)->default(0);
            $table->double('total', 15,2)->default(0);
            $table->longText('keterangan')->default('');
            $table->unsignedBigInteger('vulkanisirke')->default(0);
            $table->double('qtykeluar', 15,2)->default(0);
            $table->string('modifiedby',50)->default('');            

            $table->timestamps();

            
            $table->foreign('penerimaanstokheader_id', 'penerimaanstokdetail_penerimaanstokheader_penerimaanstokheader_id_foreign')->references('id')->on('penerimaanstokheader')->onDelete('cascade');  
            $table->foreign('stok_id', 'penerimaanstokdetail_stok_stok_id_foreign')->references('id')->on('stok');

        });
        
        DB::statement("ALTER TABLE penerimaanstokdetail NOCHECK CONSTRAINT penerimaanstokdetail_stok_stok_id_foreign");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('penerimaanstokdetail');
    }
}
