<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Historytgljatuhtempogiro extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('historytgljatuhtempogiro', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti', 50);
            $table->date('tglbukti')->nullable();
            $table->date('tgljatuhtempo')->nullable();
            $table->date('tgljatuhtempolama')->nullable();
            $table->longText('info')->nullable();
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
        Schema::dropIfExists('historytgljatuhtempogiro');
    }
}
