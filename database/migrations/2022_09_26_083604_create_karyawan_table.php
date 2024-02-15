<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKaryawanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('karyawan', function (Blueprint $table) {
            $table->id();
            $table->string('namakaryawan', 300)->nullable();
            $table->longText('keterangan')->nullable();
            $table->longText('jabatan')->nullable();
            $table->integer('statusaktif')->length(11)->nullable();
            $table->integer('statusstaff')->length(11)->nullable();
            $table->unsignedBigInteger('tas_id')->nullable();
            $table->longText('info')->nullable();            
            $table->string('modifiedby', 30)->nullable();            
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
        Schema::dropIfExists('karyawan');
    }
}
