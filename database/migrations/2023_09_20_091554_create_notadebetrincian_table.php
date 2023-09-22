<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateNotadebetrincianTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('notadebetrincian');
        
        Schema::create('notadebetrincian', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('notadebet_id')->nullable();
            $table->string('nobukti',50)->nullable();
            $table->unsignedBigInteger('agen_id')->nullable();
            $table->date('tglterima')->nullable();
            $table->double('nominal',15,2)->nullable();
            $table->longText('info')->nullable();            
            $table->string('modifiedby',50)->nullable();
            $table->timestamps();


            $table->foreign('notadebet_id', 'notadebetrincian_notadebetheader_notadebet_id_foreign')->references('id')->on('notadebetheader')->onDelete('cascade');    

        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notadebetrincian');
    }
}
