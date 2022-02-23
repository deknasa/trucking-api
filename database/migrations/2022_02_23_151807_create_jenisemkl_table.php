<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJenisEmklTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jenisemkl', function (Blueprint $table) {
            $table->id();
            $table->string('kodejenisemkl',50)->Default('');
            $table->longText('keterangan')->Default('');
            $table->string('modifiedby',50)->Default('');
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
        Schema::dropIfExists('jenisemkl');
    }
}
