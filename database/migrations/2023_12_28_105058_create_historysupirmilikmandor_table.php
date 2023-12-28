<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistorysupirmilikmandorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('historysupirmilikmandor', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supir_id')->nullable();
            $table->unsignedBigInteger('mandor_id')->nullable();
            $table->unsignedBigInteger('mandorlama_id')->nullable();
            $table->date('tglberlaku')->nullable();
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
        Schema::dropIfExists('historysupirmilikmandor');
    }
}
