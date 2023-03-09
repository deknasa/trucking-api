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
            $table->date('tglbukti')->default('1900/1/1');   
            $table->double('jumlahtrip',15,2)->default(0);
            $table->string('modifiedby',50)->default('');
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
