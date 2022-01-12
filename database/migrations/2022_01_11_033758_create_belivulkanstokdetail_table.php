<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatebelivulkanstokdetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('belivulkanstokdetail', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti', 50)->default('');
            $table->unsignedBigInteger('stok_id')->default(0);
            $table->string('satuan', 50)->default('');
            $table->double('qty', 15,2)->default(0);
            $table->double('hrgsat', 15,2)->default(0);
            $table->string('pdisc', 50)->default('');
            $table->double('ndisc', 15,2)->default(0);
            $table->double('total', 15,2)->default(0);
            $table->string('keterangan', 250)->default('');
            $table->string('modifiedby', 50)->default('');
            $table->unsignedBigInteger('gudang_id')->default(0);
            $table->string('jnsvul', 50)->default('');
            $table->integer('vulkanisirke')->length(11)->default(0);            
            $table->string('statusban', 50)->default('');
            $table->string('pgstok_nobukti', 50)->default('');
            $table->integer('vulkeawal')->length(11)->default(0);            
            $table->integer('statuspg')->length(11)->default(0);            
            $table->unsignedBigInteger('belivulkanstok_id')->default(0);
            $table->timestamps();

            $table->foreign('belivulkanstok_id')->references('id')->on('belivulkanstokheader')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('belivulkanstokdetail');
    }
}
