<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAcosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acos', function (Blueprint $table) {
            $table->id();
            $table->string('class', 50)->nullable();
            $table->string('method', 50)->nullable();
            $table->string('nama', 150)->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->integer('idheader')->length(11)->nullable();


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
        Schema::dropIfExists('acos');
    }
}
