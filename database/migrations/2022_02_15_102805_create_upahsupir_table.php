<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUpahsupirTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('upahsupir', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kotadari_id')->default('0');
            $table->unsignedBigInteger('kotasampai_id')->default('0');
            $table->double('jarak',15,2)->default('0');
            $table->unsignedBigInteger('zona_id')->default('0');
            $table->integer('statusaktif')->length(11)->default('0');
            $table->date('tglmulaiberlaku')->default('0');
            $table->integer('statusluarkota')->length(11)->default('0');
            $table->integer('statuspecahtrip')->length(11)->default('0');
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
        Schema::dropIfExists('upahsupir');
    }
}
