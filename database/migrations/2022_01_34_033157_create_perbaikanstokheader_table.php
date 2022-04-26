<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateperbaikanstokheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('perbaikanstokheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti', 50)->unique();
            $table->date('tglbukti')->default('1900/1/1');
            $table->unsignedBigInteger('supplier_id')->default(0);   
            $table->string('persentasediscount', 50)->default('');
            $table->double('nominaldiscount', 15,2)->default(0);
            $table->double('persentaseppn', 15,2)->default(0);
            $table->double('nominalppn', 15,2)->default(0);
            $table->double('total', 15,2)->default(0);
            $table->longText('keterangan')->default('');
            $table->date('tgljatuhtempo')->default('1900/1/1');
            $table->string('nobon', 50)->default('');
            $table->integer('statusedit')->length(11)->default(0);            
            $table->string('hutang_nobukti', 50)->default('');
            $table->string('modifiedby', 50)->default('');
            $table->timestamps();

            $table->foreign('supplier_id')->references('id')->on('supplier');
            $table->foreign('hutang_nobukti')->references('nobukti')->on('hutangheader');


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('perbaikanstokheader');
    }
}
