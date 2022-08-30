<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveryorderdetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deliveryorderdetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('deliveryorder_id')->default('0');
            $table->string('nobukti')->unique();
            $table->unsignedBigInteger('stok_id')->default('0');
            $table->integer('conv1')->length(11)->default('0');
            $table->integer('conv2')->length(11)->default('0');
            $table->double('qty',15,2)->default('0');
            $table->longText('keterangan')->default('');  
            $table->string('modifiedby',50)->default('');
            $table->timestamps();

            $table->foreign('deliveryorder_id')->references('id')->on('deliveryorderheader')->onDelete('cascade');             
            $table->foreign('stok_id')->references('id')->on('stok');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('deliveryorderdetail');
    }
}
