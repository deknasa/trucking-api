<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUpahritasiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('upahritasi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kotadari_id')->default('0');
            $table->unsignedBigInteger('kotasampai_id')->default('0');
            $table->double('jarak',15,2)->default('0');
            $table->unsignedBigInteger('zona_id')->default('0');
            $table->integer('statusaktif')->length(11)->default('0');
            $table->date('tglmulaiberlaku')->default('1900/1/1');
            $table->integer('statusluarkota')->length(11)->default('0');
            $table->string('modifiedby',50)->Default('');            
            $table->timestamps();

            $table->foreign('kotadari_id')->references('id')->on('kota');
            $table->foreign('kotasampai_id')->references('id')->on('kota');
            $table->foreign('zona_id')->references('id')->on('zona');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('upahritasi');
    }
}
