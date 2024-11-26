<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePenjualTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('penjual');

        Schema::create('penjual', function (Blueprint $table) {
            $table->id();
            $table->longText('namapenjual')->nullable();
            $table->longText('alamat')->nullable();
            $table->string('nohp', 50)->nullable();
            $table->string('coa', 50)->nullable();
            $table->integer('statusaktif')->length(11)->nullable();
            $table->string('modifiedby', 50)->nullable();
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
        Schema::dropIfExists('penjual');
    }
}
