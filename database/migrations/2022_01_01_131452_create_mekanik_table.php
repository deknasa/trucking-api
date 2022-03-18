<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMekanikTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mekanik', function (Blueprint $table) {
            $table->id();
            $table->string('namamekanik', 300)->default('');
            $table->longText('keterangan')->default('');
            $table->integer('statusaktif')->length(11);
            $table->string('modifiedby', 30)->default('');

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
        Schema::dropIfExists('mekanik');
    }
}
