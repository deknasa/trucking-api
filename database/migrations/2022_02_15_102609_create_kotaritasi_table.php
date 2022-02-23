<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKotaritasiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kotaritasi', function (Blueprint $table) {
            $table->id();
            $table->string('kodekota',50)->Default('');
            $table->longText('keterangan')->Default('');
            $table->integer('statusaktif')->length(11)->Default('');
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
        Schema::dropIfExists('kotaritasi');
    }
}
