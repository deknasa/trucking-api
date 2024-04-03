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
            $table->string('nobukti',50)->nullable();
            $table->unsignedBigInteger('stok_id');
            $table->double('qty', 15,2)->nullable();
            $table->double('qtyterpakai', 15,2)->nullable();
            $table->double('harga', 15,2)->nullable();
            $table->double('persentasediscount', 15,2)->nullable();
            $table->double('nominaldiscount', 15,2)->nullable();
            $table->double('total', 15,2)->nullable();
            $table->longText('keterangan')->nullable();
            $table->unsignedBigInteger('vulkanisirke')->nullable();
            $table->string('penerimaanstok_nobukti',50)->nullable();
            $table->double('qtykeluar', 15,2)->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby',50)->nullable();            

            $table->timestamps();

            
            $table->foreign('penerimaanstokheader_id', 'penerimaanstokdetail_penerimaanstokheader_penerimaanstokheader_id_foreign')->references('id')->on('penerimaanstokheader')->onDelete('cascade');  
            $table->foreign('stok_id', 'penerimaanstokdetail_stok_stok_id_foreign')->references('id')->on('stok');

            $schemaManager = Schema::getConnection()->getDoctrineSchemaManager();
            $indexesFound  = $schemaManager->listTableIndexes('penerimaanstokdetail');            

            if (! array_key_exists('penerimaanstokdetail_nobukti_index', $indexesFound)) {
                $table->index('nobukti', 'penerimaanstokdetail_nobukti_index');
            }      
            if (! array_key_exists('penerimaanstokdetail_penerimaanstokheader_id_index', $indexesFound)) {
                $table->index('penerimaanstokheader_id', 'penerimaanstokdetail_penerimaanstokheader_id_index');
            }      
            if (! array_key_exists('penerimaanstokdetail_penerimaanstok_nobukti_index', $indexesFound)) {
                $table->index('penerimaanstok_nobukti', 'penerimaanstokdetail_penerimaanstok_nobukti_index');
            }                                

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
