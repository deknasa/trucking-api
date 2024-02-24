<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSuratpengantarapprovalinputtripTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('suratpengantarapprovalinputtrip', function (Blueprint $table) {
            $table->id();
            $table->date('tglbukti')->nullable();   
            $table->integer('jumlahtrip')->length(11)->nullable();
            $table->integer('statusapproval')->length(11)->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby',50)->nullable();
            $table->dateTime('tglbatas',50)->nullable();
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
        Schema::dropIfExists('suratpengantarapprovalinputtrip');
    }
}
