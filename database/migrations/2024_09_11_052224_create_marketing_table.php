<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMarketingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::dropIfExists('marketing');

        Schema::create('marketing', function (Blueprint $table) {
            $table->id();
            $table->string('kodemarketing',50)->nullable();
            $table->longText('keterangan')->nullable();
            $table->integer('statusaktif')->length(11)->nullable();
            $table->unsignedBigInteger('tas_id')->nullable();
            $table->string('editing_by',50)->nullable();            
            $table->dateTime('editing_at')->nullable();            

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
        Schema::dropIfExists('marketing');
    }
}
