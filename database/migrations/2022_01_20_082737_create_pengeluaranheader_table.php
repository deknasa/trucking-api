<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePengeluaranheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pengeluaranheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->date('tgl')->default('1900/1/1');
            $table->unsignedBigInteger('pelanggan_id')->default('0');
            $table->longText('keterangan')->default('');
            $table->string('type',50)->default('');
            $table->string('postingdari',50)->default('');
            $table->integer('statusapproval')->Length(11)->default('0');
            $table->string('dibayarke',250)->default('');
            $table->integer('cabang')->Length(11)->default('0');
            $table->string('sumberdata',250)->default('');
            $table->string('terima_nobukti',500)->default('');
            $table->integer('statuskasbank')->Length(11)->default('0');
            $table->integer('statuspengembalian')->Length(11)->default('0');
            $table->string('userapproval',50)->default('');
            $table->date('tglapproval')->default('1900/1/1');
            $table->string('transferkeac',100)->default('');
            $table->string('transferkean',100)->default('');
            $table->string('transferkebank',100)->default('');
            $table->string('noresi',100)->default('');
            $table->integer('statusberkas')->Length(11)->default('0');
            $table->string('userberkas',100)->default('');
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
        Schema::dropIfExists('pengeluaranheader');
    }
}
