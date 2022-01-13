<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmklTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('emkl', function (Blueprint $table) {
            $table->id();
            $table->string('emkl', 30)->default('');
            $table->string('keterangan', 255)->default('');
            $table->integer('statusaktif')->length(11)->default(0);            
            $table->string('fnamaperusahaan', 100)->default('');
            $table->string('alamat', 250)->default('');
            $table->string('notelp', 100)->default('');
            $table->string('nohp', 100)->default('');
            $table->string('contactperson', 100)->default('');
            $table->double('top', 15,2)->default(0);
            $table->integer('statusapp')->length(11)->default(0);            
            $table->string('appuser', 30)->default('');
            $table->date('appdate')->default('1900/1/1');
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
        Schema::dropIfExists('emkl');
    }
}
