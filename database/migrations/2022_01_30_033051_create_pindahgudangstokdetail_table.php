<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePindahgudangstokdetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pindahgudangstokdetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pindahgudangstok_id')->default('0');
            $table->string('nobukti',50)->default('');
            $table->unsignedBigInteger('stok_id')->default('0');
            $table->integer('conv1')->length(11)->default('');
            $table->integer('conv2')->length(11)->default('');
            $table->double('qty',15,2)->default('0');
            $table->double('hrgsatuan',15,2)->default('0');
            $table->double('total',15,2)->default('0');
            $table->longText('keterangan')->default('');
            $table->integer('vulkanisirke')->length(11)->default('');
            $table->string('statusban',50)->default('');
            $table->string('keadaanban',50)->default('');
            $table->string('modifiedby',50)->default('');            
            $table->timestamps();

            $table->foreign('pindahgudangstok_id')->references('id')->on('pindahgudangstokheader')->onDelete('cascade');             
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
        Schema::dropIfExists('pindahgudangstokdetail');
    }
}
