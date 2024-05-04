<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTradotambahanabsensiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tradotambahanabsensi', function (Blueprint $table) {
            $table->id();
            $table->date ('tglabsensi')->nullable();
            $table->unsignedBigInteger('trado_id')->nullable();
            $table->integer('statusjeniskendaraan')->Length(11)->nullable();
            $table->integer('statusapproval')->Length(11)->nullable();
            $table->date('tglapproval')->nullable();
            $table->string('userapproval',50)->nullable();
            $table->longText('keterangan')->nullable();
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
        Schema::dropIfExists('tradotambahanabsensi');
    }
}
