<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHutangbayardetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hutangbayardetail', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti', 50)->default('');
            $table->double('nominal', 15,2)->default(0);
            $table->string('hutang_nobukti', 50)->default('');
            $table->integer('cicilan')->length(11)->default(0);
            $table->unsignedBigInteger('alatbayar_id')->default(0);
            $table->date('tglcair')->default('1900/1/1');
            $table->string('userid', 50)->default('');
            $table->string('modifiedby', 50)->default('');
            $table->double('potongan', 15,2)->default(0);
            $table->string('keterangan', 50)->default('');
            $table->unsignedBigInteger('coa_id')->default(0);
            $table->unsignedBigInteger('hutangbayar_id')->default(0);
            $table->timestamps();

            $table->foreign('hutangbayar_id')->references('id')->on('hutangbayarheader')->onDelete('cascade');            

        });
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
