<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotakreditdetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notakreditdetail', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->default('');
            $table->unsignedBigInteger('notakredit_id')->default('0');
            $table->date('tglterima')->default('1900/1/1');
            $table->string('invoice_bukti',50)->default('');
            $table->double('nominal',15,2)->default('0');
            $table->double('nominalbayar',15,2)->default('0');
            $table->double('Penyesuaian',15,2)->default('0');
            $table->string('keterangan',250)->default('');
            $table->string('modifiedby',50)->default('');
            $table->string('coaadjust',50)->default('');
            $table->timestamps();

            $table->foreign('notakredit_id')->references('id')->on('notakreditheader')->onDelete('cascade');                        
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notakreditdetail');
    }
}
