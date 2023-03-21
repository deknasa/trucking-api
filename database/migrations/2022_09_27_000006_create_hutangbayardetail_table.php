<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateHutangbayardetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('hutangbayardetail');

        Schema::create('hutangbayardetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hutangbayar_id')->nullable();
            $table->string('nobukti', 50)->nullable();
            $table->double('nominal', 15,2)->nullable();
            $table->string('hutang_nobukti', 50)->nullable();
            $table->integer('cicilan')->length(11)->nullable();
            $table->string('userid', 50)->nullable();
            $table->double('potongan', 15,2)->nullable();
            $table->longText('keterangan')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->timestamps();

                       
            $table->foreign('hutangbayar_id', 'hutangbayardetail_hutangbayarheader_hutangbayardetail_foreign')->references('id')->on('hutangbayarheader')->onDelete('cascade');    
            $table->foreign('hutang_nobukti', 'hutangbayardetail_hutangheader_hutang_nobukti_foreign')->references('nobukti')->on('hutangheader');    


        });
        DB::statement("ALTER TABLE hutangbayardetail NOCHECK CONSTRAINT hutangbayardetail_hutangheader_hutang_nobukti_foreign");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hutangbayardetail');
    }
}
