<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user', function (Blueprint $table) {
            $table->id();
            $table->string('user',255)->default('');
            $table->string('name',255)->default('');
            $table->string('password',255)->default('');
            $table->unsignedBigInteger('cabang_id')->default('0');
            $table->unsignedBigInteger('karyawan_id')->default('0');
            $table->string('dashboard',255)->default('');
            $table->integer('statusaktif')->length(11)->default('0');
            $table->string('modifiedby',255)->default('');
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
        Schema::dropIfExists('user');
    }
}
