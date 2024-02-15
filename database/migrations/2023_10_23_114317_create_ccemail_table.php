<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCcemailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ccemail', function (Blueprint $table) {
            $table->id();
            $table->longText('nama')->nullable();
            $table->longText('email')->nullable();
            $table->integer('statusaktif')->length(11)->nullable();
            $table->unsignedBigInteger('karyawan_id')->nullable();
            $table->unsignedBigInteger('reminderemail_id')->nullable();
            $table->unsignedBigInteger('tas_id')->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby', 50)->nullable();             
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
        Schema::dropIfExists('ccemail');
    }
}
