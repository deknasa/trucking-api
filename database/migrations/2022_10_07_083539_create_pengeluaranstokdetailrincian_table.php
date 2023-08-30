<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePengeluaranstokdetailrincianTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pengeluaranstokdetailrincian', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pengeluaranstokheader_id');
            $table->string('nobukti',50)->nullable();
            $table->unsignedBigInteger('stok_id');
            $table->double('qty', 15,2)->nullable();
            $table->double('saldoqty', 15,2)->nullable();
            $table->string('penerimaanstok_nobukti',50)->nullable();
            $table->double('penerimaanstok_harga', 15,2)->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby',50)->nullable();                
            $table->timestamps();

            $table->foreign('pengeluaranstokheader_id', 'pengeluaranstokdetailrincian_pengeluaranstokheader_pengeluaranstokheader_id_foreign')->references('id')->on('pengeluaranstokheader')->onDelete('cascade');  
            $table->foreign('stok_id', 'pengeluaranstokdetailrincian_stok_stok_id_foreign')->references('id')->on('stok');
            $table->foreign('penerimaanstok_nobukti', 'pengeluaranstokdetailrincian_penerimaanstokheader_nobukti_nobukti_foreign')->references('nobukti')->on('penerimaanstokheader');

        });

        DB::statement("ALTER TABLE pengeluaranstokdetailrincian NOCHECK CONSTRAINT pengeluaranstokdetailrincian_stok_stok_id_foreign");
        DB::statement("ALTER TABLE pengeluaranstokdetailrincian NOCHECK CONSTRAINT pengeluaranstokdetailrincian_penerimaanstokheader_nobukti_nobukti_foreign");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pengeluaranstokdetailrincian');
    }
}
