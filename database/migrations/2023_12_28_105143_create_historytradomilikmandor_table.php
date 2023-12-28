<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistorytradomilikmandorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('historytradomilikmandor', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trado_id')->nullable();
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
        Schema::dropIfExists('historytradomilikmandor');
    }
}
