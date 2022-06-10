<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceoutdetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('serviceoutdetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('serviceout_id')->default('0');
            $table->string('nobukti',50)->default('');
            $table->string('servicein_nobukti',50)->default('0');
            $table->longText('keterangan')->default('');
            $table->string('modifiedby',50)->default('');            
            $table->timestamps();

            
            $table->foreign('serviceout_id')->references('id')->on('serviceoutheader')->onDelete('cascade');             
            $table->foreign('servicein_nobukti')->references('nobukti')->on('serviceinheader');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('serviceoutdetail');
    }
}
