<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApprovalsupirketeranganTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('approvalsupirketerangan', function (Blueprint $table) {
            $table->id();
            $table->string('namasupir', 100)->nullable();
            $table->string('noktp', 30)->nullable();
            $table->integer('statusapproval')->length(11)->nullable();
            $table->date('tglbatas')->nullable();
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
        Schema::dropIfExists('approvalsupirketerangan');
    }
}
