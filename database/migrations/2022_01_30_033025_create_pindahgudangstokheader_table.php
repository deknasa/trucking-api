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
            $table->unsignedBigInteger('trado_id')->default('0');
            $table->unsignedBigInteger('gudang_id')->default('0');
            $table->longText('keterangan')->default('');
            $table->string('tipe',50)->default('');
            $table->string('nobuktido',50)->default('');
            $table->unsignedBigInteger('supplier_id')->default('0');
            $table->string('coa',50)->default('');
            $table->integer('statusvulkan')->length(11)->default('0');
            $table->string('modifiedby',50)->default('');            
            $table->timestamps();

            $table->foreign('trado_id')->references('id')->on('trado');
            $table->foreign('gudang_id')->references('id')->on('gudang');
            $table->foreign('supplier_id')->references('id')->on('supplier');
            $table->foreign('coa')->references('coa')->on('akunpusat');

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
