<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateHutangdetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('hutangdetail');

        Schema::create('hutangdetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hutang_id')->nullable();            
            $table->string('nobukti', 50)->nullable();            
            $table->date('tgljatuhtempo')->nullable();            
            $table->double('total',15,2)->nullable();            
            $table->double('cicilan',15,2)->nullable();            
            $table->longText('keterangan')->nullable();            
            $table->double('totalbayar',15,2)->nullable();            
            $table->string('modifiedby', 50)->nullable();            
            $table->timestamps();


            $table->foreign('hutang_id', 'hutangdetail_hutangheader_hutang_id_foreign')->references('id')->on('hutangheader')->onDelete('cascade');    

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
