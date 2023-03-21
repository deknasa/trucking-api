<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateakunpusatTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('akunpusat');

        Schema::create('akunpusat', function (Blueprint $table) {
            $table->id();
            $table->string('coa', 50)->unique();
            $table->longText('keterangancoa')->nullable();
            $table->string('type', 50)->nullable();
            $table->integer('level')->length(11)->nullable();
            $table->integer('statusaktif')->length(11)->nullable();
            $table->string('parent', 255)->nullable();
            $table->integer('statuscoa')->length(11)->nullable();
            $table->integer('statusaccountpayable')->length(11)->nullable();
            $table->integer('statusneraca')->length(11)->nullable();
            $table->integer('statuslabarugi')->length(11)->nullable();
            $table->string('coamain', 30)->nullable();
            $table->string('modifiedby', 30)->nullable();
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
