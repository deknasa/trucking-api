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
        Schema::create('agen', function (Blueprint $table) {
            $table->id();
            $table->string('kodeagen', 30)->default('');
            $table->string('namaagen', 30)->default('');
            $table->longText('keterangan')->default('');
            $table->integer('statusaktif')->length(11)->default(0);            
            $table->string('fnamaperusahaan', 100)->default('');
            $table->string('alamat', 250)->default('');
            $table->string('notelp', 100)->default('');
            $table->string('nohp', 100)->default('');
            $table->string('contactperson', 100)->default('');
            $table->double('top', 15,2)->default(0);
            $table->integer('statusapproval')->length(11)->default(0);            
            $table->string('userapproval', 30)->default('');
            $table->date('tglapproval')->default('1900/1/1');
            $table->integer('statustas')->length(11)->default(0);            
            $table->string('jenisemkl', 30)->default('');
            $table->string('modifiedby', 50)->default('');
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
