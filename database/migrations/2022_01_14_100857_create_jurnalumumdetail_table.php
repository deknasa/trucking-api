<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJurnalumumdetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jurnalumumdetail', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->default('');
            $table->date('tgl')->default('1900/1/1');
            $table->string('coa',50)->default('');
            $table->double('nominal',15,2)->default('0');
            $table->longText('keterangan')->default('');
            $table->string('modifiedby',50)->default('');
            $table->bigInteger('postid')->default('0');
            $table->unsignedBigInteger('jurnalumum_id')->default('0');
            $table->timestamps();

            $table->foreign('jurnalumum_id')->references('id')->on('jurnalumumheader')->onDelete('cascade');            

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('jurnalumumdetail');
    }
}
