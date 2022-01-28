<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReturbelistokdetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('returbelistokdetail', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->deaullt('');
            $table->unsignedBigInteger('returbelistok_id')->deaullt('0');
            $table->unsignedBigInteger('stok_id')->deaullt('0');
            $table->unsignedBigInteger('gudang_id')->deaullt('0');
            $table->integer('conv1')->length(11)->default('0');
            $table->integer('conv2')->length(11)->default('0');
            $table->integer('statusstok')->length(11)->default('0');
            $table->string('satuan',50)->default('');
            $table->double('qty',15,2)->default('0');
            $table->double('hrgsatuan',15,2)->default('0');            
            $table->double('persentasediscount',15,2)->default('0');            
            $table->double('nominaldiscount',15,2)->default('0');            
            $table->double('total',15,2)->default('0');            
            $table->longText('keterangan')->default('');
            $table->string('modifiedby',50)->default('');
            $table->timestamps();

            $table->foreign('returbelistok_id')->references('id')->on('returbelistokheader')->onDelete('cascade');             

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('returbelistokdetail');
    }
}
