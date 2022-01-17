<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotadebetheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notadebetheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->default('');
            $table->string('pelunasan_nobukti',50)->default('');
            $table->date('tgl')->default('1900/1/1');
            $table->string('keterangan',250)->default('');
            $table->string('postfrom',150)->default('');
            $table->integer('app')->length(11)->default('0');
            $table->date('tgllunas')->default('1900/1/1');
            $table->string('appuserid',50)->default('');
            $table->date('appdate')->default('1900/1/1');
            $table->string('noresi',50)->default('');
            $table->integer('berkas')->length(11)->default('0');
            $table->string('berkasuser',50)->default('');
            $table->date('berkastgl')->default('1900/1/1');
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
        Schema::dropIfExists('notadebetheader');
    }
}
