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
            $table->unsignedBigInteger('kotadari_id')->Default('0');
            $table->unsignedBigInteger('kotasampai_id')->Default('0');
            $table->double('harga',15,2)->Default('0');
            $table->integer('statusaktif')->length(11)->Default('0');
            $table->double('jarak',15,2)->Default('0');
            $table->double('liter20',15,2)->Default('0');
            $table->double('liter40',15,2)->Default('0');
            $table->double('liter2x20',15,2)->Default('0');
            $table->double('nominaltol',15,2)->Default('0');
            $table->string('modifiedby',50)->Default('');
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
        Schema::dropIfExists('upahritasi');
    }
}
