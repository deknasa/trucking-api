<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('user');

        Schema::create('user', function (Blueprint $table) {
            $table->id();
            $table->string('user',255)->nullable();
            $table->string('name',255)->nullable();
            $table->string('password',255)->nullable();
            $table->unsignedBigInteger('cabang_id')->nullable();
            $table->unsignedBigInteger('karyawan_id')->nullable();
            $table->string('dashboard',255)->nullable();
            $table->integer('statusaktif')->length(11)->nullable();
            $table->integer('statusakses')->length(11)->nullable();
            $table->string('email',255)->nullable();
            $table->string('modifiedby',255)->nullable();
            $table->timestamps();

            $table->foreign('cabang_id', 'user_cabang_cabang_id_foreign')->references('id')->on('cabang');
        });
        
        DB::statement("ALTER TABLE [user] NOCHECK CONSTRAINT user_cabang_cabang_id_foreign");
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
