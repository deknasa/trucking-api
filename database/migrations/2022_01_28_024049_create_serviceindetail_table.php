<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceindetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('serviceindetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('servicein_id')->default('0');
            $table->string('nobukti',50)->default('');
            $table->unsignedBigInteger('mekanik_id')->default('0');
            $table->longText('keterangan')->default('');
            $table->string('modifiedby',50)->default('');
            $table->timestamps();

            $table->foreign('servicein_id')->references('id')->on('serviceinheader')->onDelete('cascade');             
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('serviceindetail');
    }
}
