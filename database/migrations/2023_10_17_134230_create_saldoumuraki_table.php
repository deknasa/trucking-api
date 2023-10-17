<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSaldoumurakiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('saldoumuraki', function (Blueprint $table) {
            $table->id();
            $table->Integer('stok_id')->nullable();
            $table->date('tglawal')->nullable();
            $table->integer('jumlahharitrip')->nullable();
            $table->longText('info')->nullable();            
            $table->string('modifiedby',50)->nullable();            
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
        Schema::dropIfExists('saldoumuraki');
    }
}
