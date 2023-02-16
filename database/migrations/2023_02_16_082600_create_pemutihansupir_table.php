<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePemutihansupirTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pemutihansupir', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->date('tglbukti')->default('1900/1/1');   
            $table->unsignedBigInteger('supir_id')->default(0);
            $table->double('pengeluaransupir',15,2)->default(0);
            $table->double('penerimaansupir',15,2)->default(0);
            $table->unsignedBigInteger('statusformat')->default(0);
            $table->string('modifiedby',50)->default('');
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
        Schema::dropIfExists('pemutihansupir');
    }
}
