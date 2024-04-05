<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgenTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::dropIfExists('agen');
        
        Schema::create('agen', function (Blueprint $table) {
            $table->id();
            $table->string('kodeagen', 30)->nullable();
            $table->string('namaagen', 100)->nullable();
            $table->longText('keterangan')->nullable();
            $table->integer('statusaktif')->length(11)->nullable();            
            $table->string('coa', 50)->nullable();
            $table->string('coapendapatan', 50)->nullable();
            $table->string('namaperusahaan', 100)->nullable();
            $table->string('alamat', 250)->nullable();
            $table->string('notelp', 100)->nullable();
            $table->string('nohp', 100)->nullable();
            $table->string('contactperson', 100)->nullable();
            $table->double('top', 15,2)->nullable();
            $table->integer('statusapproval')->length(11)->nullable();            
            $table->string('userapproval', 30)->nullable();
            $table->date('tglapproval')->nullable();
            $table->integer('statustas')->length(11)->nullable();            
            $table->string('jenisemkl', 30)->nullable();
            $table->unsignedBigInteger('tas_id')->nullable();
            $table->string('editing_by',50)->nullable();            
            $table->dateTime('editing_at')->nullable();            

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
        Schema::dropIfExists('agen');
    }
}
