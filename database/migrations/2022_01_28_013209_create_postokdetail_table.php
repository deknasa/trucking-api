<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostokdetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('postokdetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('postok_id')->default('0');
            $table->string('nobukti')->unique();
            $table->unsignedBigInteger('stok_id')->default('0');
            $table->integer('conv1')->length(11)->default('0');
            $table->integer('conv2')->length(11)->default('0');
            $table->integer('statusstok')->length(11)->default('0');
            $table->string('satuan',50)->default('');
            $table->double('qty',15,2)->default('0');
            $table->double('hrgsatuan',15,2)->default('0');
            $table->double('total',15,2)->default('0');
            $table->longText('keterangan')->default('');
            $table->string('modifiedby',50)->default('');
            $table->timestamps();

            $table->foreign('postok_id')->references('id')->on('postokheader')->onDelete('cascade');             

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('postokdetail');
    }
}
