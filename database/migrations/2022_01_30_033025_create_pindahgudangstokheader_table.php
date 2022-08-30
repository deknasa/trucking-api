<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePindahgudangstokheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pindahgudangstokheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->date('tglbukti')->default('1900/1/1');
            $table->unsignedBigInteger('gudangdari_id')->default('0');
            $table->longText('keterangan')->default('');
            $table->string('modifiedby',50)->default('');            
            $table->timestamps();

            $table->foreign('gudangdari_id')->references('id')->on('gudang');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pindahgudangstokheader');
    }
}
