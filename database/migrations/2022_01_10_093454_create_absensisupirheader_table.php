<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateabsensisupirheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('absensisupirheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti', 50)->default('');
            $table->date('tgl')->default('1900/1/1');
            $table->longText('keterangan', 8000)->default('');
            $table->string('kasgantung_nobukti', 50)->default('');
            $table->double('fnominal',15,2)->default(0);
            $table->string('modifiedby', 50)->default('');
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
        Schema::dropIfExists('absensisupirheader');
    }
}
