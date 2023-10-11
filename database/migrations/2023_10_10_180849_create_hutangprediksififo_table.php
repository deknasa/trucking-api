<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHutangprediksififoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hutangprediksififo', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->nullable();
            $table->string('nobukti_id',50)->nullable();
            $table->unsignedBigInteger('urut')->nullable();
            $table->double('nominal',15,2)->nullable();
            $table->string('penerimaanhutangprediksi_nobukti',50)->nullable();
            $table->double('penerimaanhutangprediksi_nominal',15,2)->nullable();
            $table->longText('info')->nullable();            
            $table->string('modifiedby',50)->nullable();
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
        Schema::dropIfExists('hutangprediksififo');
    }
}
