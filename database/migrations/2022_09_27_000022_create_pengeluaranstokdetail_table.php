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
            $table->double('persentasediscount', 15,2)->nullable();
            $table->double('nominaldiscount', 15,2)->nullable();
            $table->double('total', 15,2)->nullable();
            $table->longText('keterangan')->nullable();
            $table->unsignedBigInteger('vulkanisirke')->nullable();
            $table->integer('statusservicerutin')->length(11)->nullable();
            $table->integer('statusoli')->length(11)->nullable();
            $table->string('pengeluaranstok_nobukti',50)->nullable();
            $table->longText('info')->nullable();            
            $table->string('modifiedby',50)->nullable();            
            $table->timestamps();

                      
            $table->foreign('pengeluaranstokheader_id', 'pengeluaranstokdetail_pengeluaranstokheader_pengeluaranstokheader_id_foreign')->references('id')->on('pengeluaranstokheader')->onDelete('cascade');  
            $table->foreign('stok_id', 'pengeluaranstokdetail_stok_stok_id_foreign')->references('id')->on('stok');

            $schemaManager = Schema::getConnection()->getDoctrineSchemaManager();
            $indexesFound  = $schemaManager->listTableIndexes('pengeluaranstokdetail');            

            if (! array_key_exists('pengeluaranstokdetail_nobukti_index', $indexesFound)) {
                $table->index('nobukti', 'pengeluaranstokdetail_nobukti_index');
            }  

            if (! array_key_exists('pengeluaranstokdetail_pengeluaranstokheader_id_index', $indexesFound)) {
                $table->index('pengeluaranstokheader_id', 'pengeluaranstokdetail_pengeluaranstokheader_id_index');
            } 

            if (! array_key_exists('pengeluaranstokdetail_stok_id_index', $indexesFound)) {
                $table->index('stok_id', 'pengeluaranstokdetail_stok_id_index');
            }              
            if (! array_key_exists('pengeluaranstokdetail_vulkanisirke_index', $indexesFound)) {
                $table->index('vulkanisirke', 'pengeluaranstokdetail_vulkanisirke_index');
            }             
            if (! array_key_exists('pengeluaranstokdetail_pengeluaranstok_nobukti_index', $indexesFound)) {
                $table->index('pengeluaranstok_nobukti', 'pengeluaranstokdetail_pengeluaranstok_nobukti_index');
            }               
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
