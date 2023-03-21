<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogtrailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('logtrail', function (Blueprint $table) {
            $table->id();
            $table->string('namatabel',50)->nullable();
            $table->string('postingdari',200)->nullable();
            $table->unsignedBigInteger('idtrans')->nullable();
            $table->string('nobuktitrans',150)->nullable();
            $table->string('aksi',30)->nullable();
            $table->longText('datajson')->nullable();
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
        Schema::dropIfExists('logtrail');
    }
}
