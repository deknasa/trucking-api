<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHutangdetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hutangdetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hutang_id')->default(0);            
            $table->string('nobukti', 50)->default('');            
            $table->unsignedBigInteger('supplier_id')->default(0);            
            $table->date('tgljatuhtempo')->default('1900/1/1');            
            $table->double('total',15,2)->default('0');            
            $table->double('cicilan',15,2)->default('0');            
            $table->longText('keterangan')->default('');            
            $table->double('totalbayar',15,2)->default('0');            
            $table->string('modifiedby', 50)->default('');            
            $table->timestamps();

            $table->foreign('hutang_id')->references('id')->on('hutangheader')->onDelete('cascade');            
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hutangdetail');
    }
}
