<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateHutanglamadetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hutanglamadetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hutanglama_id')->nullable();            
            $table->string('nobukti', 50)->nullable();            
            $table->date('tgljatuhtempo')->nullable();            
            $table->double('total',15,2)->nullable();            
            $table->double('cicilan',15,2)->nullable();            
            $table->longText('keterangan')->nullable();            
            $table->double('totalbayar',15,2)->nullable();    
            $table->longText('info')->nullable();                    
            $table->string('modifiedby', 50)->nullable();                
            $table->timestamps();

            $table->foreign('hutanglama_id', 'hutanglamadetail_hutanglamaheader_hutanglama_id_foreign')->references('id')->on('hutanglamaheader')->onDelete('cascade');                
        });

        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hutanglamadetail');
    }
}
