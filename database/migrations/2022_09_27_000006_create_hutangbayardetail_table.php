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
            $table->unsignedBigInteger('hutangbayar_id')->default(0);
            $table->string('nobukti', 50)->default('');
            $table->double('nominal', 15,2)->default(0);
            $table->string('hutang_nobukti', 50)->default('');
            $table->integer('cicilan')->length(11)->default(0);
            $table->unsignedBigInteger('alatbayar_id')->default(0);
            $table->date('tglcair')->default('1900/1/1');
            $table->string('userid', 50)->default('');
            $table->double('potongan', 15,2)->default(0);
            $table->longText('keterangan')->default('');
            $table->unsignedBigInteger('coa_id')->default(0);
            $table->string('modifiedby', 50)->default('');
            $table->timestamps();

                       
            $table->foreign('hutangbayar_id', 'hutangbayardetail_hutangbayarheader_hutangbayardetail_foreign')->references('id')->on('hutangbayarheader')->onDelete('cascade');    
            $table->foreign('alatbayar_id', 'hutangbayardetail_alatbayar_alatbayar_id_foreign')->references('id')->on('alatbayar');    
            $table->foreign('hutang_nobukti', 'hutangbayardetail_hutangheader_hutang_nobukti_foreign')->references('nobukti')->on('hutangheader');    


        });
        DB::statement("ALTER TABLE hutangbayardetail NOCHECK CONSTRAINT hutangbayardetail_alatbayar_alatbayar_id_foreign");
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
