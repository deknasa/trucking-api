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
            $table->unsignedBigInteger('rekappenerimaan_id')->default('0');
            $table->string('nobukti',50)->default('');
            $table->string('penerimaan_nobukti',50)->default('');
            $table->date('tgltransaksi')->default('1900/1/1');
            $table->double('nominal',15,2)->default('0');
            $table->longText('keterangan')->default('');
            $table->string('modifiedby',50)->default('');            
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
