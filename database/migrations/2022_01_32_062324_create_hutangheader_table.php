<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHutangheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hutangheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti', 50)->unique();            
            $table->date('tglbukti')->default('1900/1/1');            
            $table->longText('keterangan')->default('');            
            $table->string('coa',50)->default('');            
            $table->double('total',15,2)->default(0);            
            $table->string('postingdari', 50)->default('');            
            $table->string('modifiedby', 50)->default('');            
            $table->timestamps();

            $table->foreign('coa')->references('coa')->on('akunpusat');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hutangheader');
    }
}
