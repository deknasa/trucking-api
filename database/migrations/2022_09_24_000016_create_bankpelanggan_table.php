<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatebankpelangganTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::dropIfExists('bankpelanggan');
        
        Schema::create('bankpelanggan', function (Blueprint $table) {
            $table->id();
            $table->string('kodebank', 50)->default('');
            $table->string('namabank', 100)->default('');
            $table->longText('keterangan')->default('');
            $table->integer('statusaktif')->length(11)->default(0);            
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
        Schema::dropIfExists('bankpelanggan');
    }
}
