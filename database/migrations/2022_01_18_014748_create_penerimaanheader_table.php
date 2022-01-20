<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePenerimaanheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('penerimaanheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->date('tgl')->default('1900/1/1');
            $table->unsignedBigInteger('pelanggan_id')->default('0');
            $table->longText('keterangan')->default('');
            $table->string('postingdari',50)->default('');
            $table->string('diterimadari',100)->default('');
            $table->date('tgllunas')->default('1900/1/1');
            $table->unsignedBigInteger('cabang_id')->default('0');
            $table->integer('statuskas')->length(11)->default('0');
            $table->integer('statusapproval')->length(11)->default('0');
            $table->string('userapproval',50)->default('');
            $table->date('tglapproval')->default('1900/1/1');
            $table->string('noresi',50)->default('');
            $table->integer('statusberkas')->length(11)->default('0');
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
        Schema::dropIfExists('penerimaanheader');
    }
}
