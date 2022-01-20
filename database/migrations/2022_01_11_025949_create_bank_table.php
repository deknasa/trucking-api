<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatebankTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bank', function (Blueprint $table) {
            $table->id();
            $table->string('kodebank', 50)->default('');
            $table->string('namabank', 50)->default('');
            $table->string('coa', 50)->default('');
            $table->string('tipe', 50)->default('');
            $table->integer('statusaktif')->length(11)->default(0);
            $table->integer('kodepenerimaan')->length(11)->default(0);
            $table->integer('kodepengeluaran')->length(11)->default(0);
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
        Schema::dropIfExists('bank');
    }
}
