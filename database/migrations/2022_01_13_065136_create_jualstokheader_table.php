<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJualstokheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jualstokheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->default('');
            $table->date('tgl')->default('1900/1/1');
            $table->unsignedBigInteger('pelanggan_id')->default('0');
            $table->double('total',15,2)->default('0');
            $table->string('keterangan',250)->default('');
            $table->string('modifiedby',50)->default('');
            $table->string('kasmasuk_nobukti',50)->default('');
            $table->unsignedBigInteger('coa_id')->default('0');
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
        Schema::dropIfExists('jualstokheader');
    }
}
