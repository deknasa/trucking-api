<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatebelistokdetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('belistokdetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('belistok_id')->default(0);            
            $table->string('nobukti', 50)->default('');
            $table->unsignedBigInteger('stok_id')->default(0);            
            $table->integer('conv1')->length(11)->default('0');
            $table->integer('conv2')->length(11)->default('0');
            $table->integer('statusstok')->length(11)->default('0');            
            $table->string('satuan', 50)->default('');
            $table->double('qty', 15,2)->default(0);
            $table->double('hrgsatuan', 15,2)->default(0);
            $table->string('persentasediscount', 50)->default('');
            $table->double('nominaldiscount', 15,2)->default(0);
            $table->double('total', 15,2)->default(0);
            $table->longText('keterangan')->default('');
            $table->unsignedBigInteger('gudang_id')->default(0);            
            $table->string('jenisvulkanisir', 50)->default('');
            $table->integer('vulkanisirke')->length(11)->default(0);
            $table->string('statusban', 50)->default('');
            $table->string('pindahgudangstok_nobukti', 50)->default('');
            $table->integer('vulkankeawal')->length(11)->default(0);
            $table->integer('statuspindahgudang')->length(11)->default(0);
            $table->string('modifiedby', 50)->default('');
            $table->timestamps();

            $table->foreign('belistok_id')->references('id')->on('belistokheader')->onDelete('cascade');
            $table->foreign('gudang_id')->references('id')->on('gudang');
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
        Schema::dropIfExists('belistokdetail');
    }
}
