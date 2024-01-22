<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePengeluaranstokdetailfifoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pengeluaranstokdetailfifo', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pengeluaranstokheader_id');
            $table->string('nobukti',50)->nullable();
            $table->unsignedBigInteger('stok_id')->nullable();
            $table->unsignedBigInteger('gudang_id')->nullable();
            $table->unsignedBigInteger('urut')->nullable();
            $table->double('qty',15,2)->nullable();
            $table->string('penerimaanstokheader_nobukti',50)->nullable();
            $table->double('penerimaanstok_qty',15,2)->nullable();
            $table->double('penerimaanstok_harga',15,2)->nullable();
            $table->double('penerimaanstok_total',15,2)->nullable();            
            $table->double('penerimaanstok_totalterpakai',15,2)->nullable();            
            $table->double('penerimaanstokheader_total',15,2)->nullable();
            $table->double('penerimaanstokheader_totalterpakai',15,2)->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby',50)->nullable();
            $table->timestamps();

            $table->foreign('pengeluaranstokheader_id', 'pengeluaranstokdetailfifo_pengeluaranstokheader_penerimaanstokheader_id_foreign')->references('id')->on('pengeluaranstokheader')->onDelete('cascade');  
            $table->foreign('stok_id', 'pengeluaranstokdetailfifo_stok_stok_id_foreign')->references('id')->on('stok');            
            $table->foreign('gudang_id', 'pengeluaranstokdetailfifo_gudang_gudang_id_foreign')->references('id')->on('gudang');            
            $table->foreign('penerimaanstokheader_nobukti', 'pengeluaranstokdetailfifo_penerimaanstokheader_pengeluaranstokheader_nobukti_foreign')->references('nobukti')->on('penerimaanstokheader');            
        });

        DB::statement("ALTER TABLE pengeluaranstokdetailfifo NOCHECK CONSTRAINT pengeluaranstokdetailfifo_stok_stok_id_foreign");
        DB::statement("ALTER TABLE pengeluaranstokdetailfifo NOCHECK CONSTRAINT pengeluaranstokdetailfifo_gudang_gudang_id_foreign");
        DB::statement("ALTER TABLE pengeluaranstokdetailfifo NOCHECK CONSTRAINT pengeluaranstokdetailfifo_penerimaanstokheader_pengeluaranstokheader_nobukti_foreign");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pengeluaranstokdetailfifo');
    }
}
