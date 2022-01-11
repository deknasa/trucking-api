<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTakunpusatTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('takunpusat', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50)->default('');
            $table->integer('level')->length(11)->default(0);
            $table->string('coa', 30)->default('');
            $table->string('keterangancoa', 255)->default('');
            $table->integer('aktif')->length(11)->default(0);
            $table->string('parent', 255)->default('');
            $table->integer('ap')->length(11)->default(0);
            $table->integer('neraca')->length(11)->default(0);
            $table->integer('labarugi')->length(11)->default(0);
            $table->string('coamain', 30)->default('');
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
        Schema::dropIfExists('takunpusat');
    }
}
