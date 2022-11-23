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
            $table->date('tglbukti')->default('1900/1/1');
            $table->longText('keterangan')->default('');
            $table->string('postingdari',50)->default('');
            $table->integer('statusapproval')->length(11)->default('0');
            $table->string('userapproval',50)->default('');
            $table->dateTime('tglapproval')->default('1900/1/1');
            $table->unsignedBigInteger('statusformat')->default(0);              
            $table->string('modifiedby',50)->default('');            
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
