<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceoutheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('serviceoutheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->date('tglbukti')->default('1900/1/1');
            $table->unsignedBigInteger('trado_id')->default('0');
            $table->date('tglkeluar')->default('1900/1/1');
            $table->longText('keterangan')->default('');
            $table->string('modifiedby',50)->unique();
            $table->timestamps();

            $table->foreign('trado_id')->references('id')->on('trado');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('serviceoutheader');
    }
}
