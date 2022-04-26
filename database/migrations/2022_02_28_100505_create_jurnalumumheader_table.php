<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJurnalumumheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jurnalumumheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->date('tglbukti')->default('1900/1/1');
            $table->longText('keterangan')->default('');
            $table->string('postingdari',50)->default('');
            $table->integer('statusapproval')->length(11)->default('0');
            $table->string('userapproval',50)->default('');
            $table->dateTime('tglapproval')->default('1900/1/1');
            $table->string('modifiedby',50)->default('');
            $table->timestamps();

             
            $table->foreign('nobukti')->references('nobukti')->on('penerimaanheader');            

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('jurnalumumheader');
    }
}
