<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMandordetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mandordetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mandor_id')->nullable();            
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('tas_id')->nullable();
            $table->string('modifiedby',50)->nullable();
            $table->longText('info')->nullable();            
            $table->timestamps();

            $table->foreign('mandor_id', 'mandordetail_mandor_mandor_id_foreign')->references('id')->on('mandor')->onDelete('cascade');    

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mandordetail');
    }
}
