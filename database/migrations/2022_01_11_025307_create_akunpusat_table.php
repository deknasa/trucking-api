<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateakunpusatTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('akunpusat', function (Blueprint $table) {
            $table->id();
            $table->string('coa', 30)->default('');
            $table->longText('keterangancoa')->default('');
            $table->string('type', 50)->default('');
            $table->integer('level')->length(11)->default(0);
            $table->integer('aktif')->length(11)->default(0);
            $table->string('parent', 255)->default('');
            $table->integer('statusaccountpayable')->length(11)->default(0);
            $table->integer('statusneraca')->length(11)->default(0);
            $table->integer('statuslabarugi')->length(11)->default(0);
            $table->string('coamain', 30)->default('');
            $table->string('modifiedby', 30)->default('');
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
        Schema::dropIfExists('akunpusat');
    }
}
