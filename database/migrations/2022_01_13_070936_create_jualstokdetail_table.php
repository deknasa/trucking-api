<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJualstokdetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jualstokdetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('jualstok_id')->default('0');
            $table->string('nobukti',50)->default('');
            $table->unsignedBigInteger('gudang_id')->default('0');
            $table->unsignedBigInteger('stok_id')->default('0');
            $table->integer('conv1')->length(11)->default('');
            $table->integer('conv2')->length(11)->default('');
            $table->integer('statusstok')->length(11)->default('');            $table->string('satuan',50)->default('');
            $table->double('qty',15,2)->default('0');
            $table->double('hrgsatuan',15,2)->default('0');
            $table->double('total',15,2)->default('0');
            $table->longtext('keterangan')->default('');
            $table->string('modifiedby',50)->default('');
            $table->timestamps();

            $table->foreign('jualstok_id')->references('id')->on('jualstokheader')->onDelete('cascade');            
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('jualstokdetail');
    }
}
