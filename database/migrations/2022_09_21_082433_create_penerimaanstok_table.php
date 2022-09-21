<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePenerimaanstokTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('penerimaanstok', function (Blueprint $table) {
            $table->id();
            $table->longText('kodepenerimaan')->default('');            
            $table->longText('keterangan')->default('');            
            $table->string('coa',50)->default('');            
            $table->unsignedBigInteger('statusformat')->default(0);            
            $table->string('modifiedby',50)->default('');              
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
        Schema::dropIfExists('penerimaanstok');
    }
}
