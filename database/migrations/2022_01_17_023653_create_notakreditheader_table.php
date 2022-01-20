<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotakreditheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notakreditheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->string('pelunasan_nobukti',50)->default('');
            $table->date('tgl')->default('1900/1/1');
            $table->string('keterangan',250)->default('');
            $table->string('postingdari',50)->default('');
            $table->integer('statusapproval')->length(11)->default('0');
            $table->date('tgllunas')->default('1900/1/1');
            $table->string('userapproval',50)->default('');
            $table->date('tglapproval')->default('1900/1/1');
            $table->string('noresi',50)->default('');
            $table->integer('berkas')->length(11)->default('0');
            $table->string('userberkas',50)->default('');
            $table->date('tglberkas')->default('1900/1/1');
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
        Schema::dropIfExists('notakreditheader');
    }
}
