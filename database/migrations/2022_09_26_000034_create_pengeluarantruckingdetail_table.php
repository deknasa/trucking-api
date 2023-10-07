<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePengeluarantruckingdetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('pengeluarantruckingdetail');

        Schema::create('pengeluarantruckingdetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pengeluarantruckingheader_id');
            $table->string('nobukti',50)->nullable();
            $table->unsignedBigInteger('karyawan_id')->nullable();
            $table->unsignedBigInteger('supir_id')->nullable();
            $table->unsignedBigInteger('trado_id')->nullable();
            $table->unsignedBigInteger('stok_id')->nullable();
            $table->string('pengeluaranstok_nobukti',50)->nullable();
            $table->string('penerimaanstok_nobukti',50)->nullable();
            $table->double('qty', 15,2)->nullable();
            $table->double('harga', 15,2)->nullable();
            $table->double('total', 15,2)->nullable();
            $table->string('penerimaantruckingheader_nobukti',50)->nullable();
            $table->string('invoice_nobukti',50)->nullable();
            $table->string('orderantrucking_nobukti', 50)->nullable();
            $table->string('suratpengantar_nobukti', 50)->nullable();
            $table->double('nominal',15,2)->nullable();        
            $table->double('nominaltagih',15,2)->nullable();        
            $table->longText('keterangan')->nullable();    
            $table->double('nominaltambahan',15,2)->nullable();        
            $table->longText('keterangantambahan')->nullable();    
            $table->integer('statustitipanemkl')->length(11)->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby',50)->nullable();                  
            $table->timestamps();

      
            $table->foreign('pengeluarantruckingheader_id', 'pengeluarantruckingdetail_pengeluarantruckingheader_pengeluarantruckingheader_id_foreign')->references('id')->on('pengeluarantruckingheader')->onDelete('cascade');    
            $table->foreign('supir_id', 'pengeluarantruckingdetail__supir_supir_id_foreign')->references('id')->on('supir');    

        });

        DB::statement("ALTER TABLE pengeluarantruckingdetail NOCHECK CONSTRAINT pengeluarantruckingdetail__supir_supir_id_foreign");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pengeluarantruckingdetail');
    }
}
