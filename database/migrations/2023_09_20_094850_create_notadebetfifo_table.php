<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotadebetfifoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('notadebetfifo');
        
        Schema::create('notadebetfifo', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('notadebet_id')->nullable();
            $table->string('notadebet_nobukti',50)->nullable();
            $table->unsignedBigInteger('pelunasanpiutang_id')->nullable();
            $table->string('pelunasanpiutang_nobukti',50)->nullable();
            $table->unsignedBigInteger('agen_id')->nullable();
            $table->double('nominal',15,2)->nullable();
            $table->double('notadebet_nominal',15,2)->nullable();
            $table->integer('urut')->nullable();
            $table->longText('info')->nullable();            
            $table->string('modifiedby',50)->nullable();
            $table->timestamps();

            $table->foreign('pelunasanpiutang_id', 'notadebetfifo_pelunasanpiutangheader_pelunasanpiutang_id_foreign')->references('id')->on('pelunasanpiutangheader')->onDelete('cascade');  


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notadebetfifo');
    }
}
