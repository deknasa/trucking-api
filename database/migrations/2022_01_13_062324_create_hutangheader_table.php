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
            $table->date('tgl')->default('1900/1/1');            
            $table->longText('keterangan')->default('');            
            $table->unsignedBigInteger('coa_id')->default(0);            
            $table->double('total',15,2)->default(0);            
            $table->string('postingdari', 50)->default('');            
            $table->string('modifiedby', 50)->default('');            
            $table->timestamps();
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
