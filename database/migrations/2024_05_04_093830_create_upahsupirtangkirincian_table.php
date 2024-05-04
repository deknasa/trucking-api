<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateUpahsupirtangkirincianTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('upahsupirtangkirincian', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('upahsupirtangki_id')->nullable();
            $table->unsignedBigInteger('triptangki_id')->nullable();
            $table->double('nominalsupir',15,2)->nullable();
            $table->unsignedBigInteger('tas_id')->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby',50)->nullable();
            $table->timestamps();

            $table->foreign('upahsupirtangki_id', 'upahsupirtangkirincian_upahsupirtangki_upahsupirtangki_id_foreign')->references('id')->on('upahsupirtangki')->onDelete('cascade');
            $table->foreign('triptangki_id', 'upahsupirtangkirincian_triptangki_triptangki_id_foreign')->references('id')->on('triptangki');

        });

        DB::statement("ALTER TABLE upahsupirtangkirincian NOCHECK CONSTRAINT tangkirincian_triptangki_triptangki_id_foreign");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('upahsupirtangkirincian');
    }
}
