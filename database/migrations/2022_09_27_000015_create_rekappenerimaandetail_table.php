<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateRekappenerimaandetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('rekappenerimaandetail');

        Schema::create('rekappenerimaandetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rekappenerimaan_id')->nullable();
            $table->string('nobukti',50)->nullable();
            $table->string('penerimaan_nobukti',50)->nullable();
            $table->date('tgltransaksi')->nullable();
            $table->double('nominal',15,2)->nullable();
            $table->longText('keterangan')->nullable();
            $table->string('modifiedby',50)->nullable();            
            $table->timestamps();

            $table->foreign('rekappenerimaan_id', 'rekappenerimaandetail_rekappenerimaanheader_rekappenerimaan_id_foreign')->references('id')->on('rekappenerimaanheader')->onDelete('cascade');    
            $table->foreign('penerimaan_nobukti', 'rekappenerimaandetail_penerimaanheader_penerimaan_nobukti_foreign')->references('nobukti')->on('penerimaanheader');    

        });
        DB::statement("ALTER TABLE rekappenerimaandetail NOCHECK CONSTRAINT rekappenerimaandetail_penerimaanheader_penerimaan_nobukti_foreign");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rekappenerimaandetail');
    }
}
