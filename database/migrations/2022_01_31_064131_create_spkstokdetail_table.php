<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpkstokdetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('spkstokdetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('spkstok_id')->default('0');
            $table->string('nobukti')->unique();
            $table->unsignedBigInteger('stok_id')->default('0');
            $table->integer('conv1')->length(11)->default('0');
            $table->integer('conv2')->length(11)->default('0');
            $table->double('qty',15,2)->default('0');
            $table->double('hrgsatuan',15,2)->default('0');  
            $table->double('total',15,2)->default('0');  
            $table->longText('keterangan')->default('');  
            $table->string('coa',50)->default('0');
            $table->integer('statusoli')->length(11)->default('0');
            $table->integer('vulke')->length(11)->default('0');
            $table->integer('statusban')->length(11)->default('0');
            $table->string('kodebanasal',50)->default('');
            $table->string('jenisvulkanisir',50)->default('');
            $table->string('pindahgudang_nobukti',50)->default('');
            $table->string('keadaanban',50)->default('');
            $table->string('modifiedby',50)->default('');

            $table->timestamps();

            $table->foreign('spkstok_id')->references('id')->on('spkstokheader')->onDelete('cascade');             
            $table->foreign('stok_id')->references('id')->on('stok');
            $table->foreign('coa')->references('coa')->on('akunpusat');
            $table->foreign('pindahgudang_nobukti')->references('nobukti')->on('pindahgudangstokheader');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('spkstokhdetail');
    }
}
