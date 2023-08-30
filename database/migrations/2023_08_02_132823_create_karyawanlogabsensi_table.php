<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKaryawanlogabsensiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('karyawanlogabsensi', function (Blueprint $table) {
            $table->id();
            $table->integer('idabsen')->nullable();
            $table->date('tglresign')->nullable();
            $table->integer('statusaktif')->length(11)->nullable();            
            $table->longText('info')->nullable();
            $table->string('modifiedby', 30)->nullable();                    
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
        Schema::dropIfExists('karyawanlogabsensi');
    }
}
