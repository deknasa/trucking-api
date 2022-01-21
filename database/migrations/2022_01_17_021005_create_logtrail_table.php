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
            $table->string('namatabel',50)->default('');
            $table->string('postingdari',200)->default('');
            $table->unsignedBigInteger('idtrans')->default('0');
            $table->string('nobuktitrans',150)->default('');
            $table->string('aksi',30)->default('');
            $table->longText('datajson')->default('');
            $table->string('modifiedby',50)->default('');
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
