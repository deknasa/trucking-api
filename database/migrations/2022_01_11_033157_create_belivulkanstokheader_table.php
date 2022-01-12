<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatebelivulkanstokheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('belivulkanstokheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti', 50)->default('');
            $table->date('tgl')->default('1900/1/1');
            $table->unsignedBigInteger('supplier_id')->default(0);   
            $table->string('pdisc', 50)->default('');
            $table->double('ndisc', 15,2)->default(0);
            $table->double('pppn', 15,2)->default(0);
            $table->double('nppn', 15,2)->default(0);
            $table->double('total', 15,2)->default(0);
            $table->string('keterangan', 250)->default('');
            $table->string('modifiedby', 50)->default('');
            $table->date('tgljthhtg')->default('1900/1/1');
            $table->string('nobon', 50)->default('');
            $table->integer('statusedit')->length(11)->default(0);            
            $table->string('hutang_nobukti', 50)->default('');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('belivulkanstokheader');
    }
}
