<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateJurnalumumpusatheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('jurnalumumpusatheader');
        
        Schema::create('jurnalumumpusatheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->date('tglbukti')->nullable();
            $table->longText('keterangan')->nullable();            
            $table->string('postingdari',50)->nullable();
            $table->integer('statusapproval')->length(11)->nullable();
            $table->string('userapproval',50)->nullable();
            $table->dateTime('tglapproval')->nullable();
            $table->unsignedBigInteger('statusformat')->nullable();              
            $table->string('cabang',500)->nullable();            
            $table->longText('info')->nullable();
            $table->string('modifiedby',50)->nullable();            
            $table->timestamps();

            $table->foreign('nobukti', 'jurnalumumpusatheader_jurnalumumheader_nobukti_foreign')->references('nobukti')->on('jurnalumumheader');            

        });

        DB::statement("ALTER TABLE jurnalumumpusatheader NOCHECK CONSTRAINT jurnalumumpusatheader_jurnalumumheader_nobukti_foreign");

        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('jurnalumumpusatheader');
    }
}
