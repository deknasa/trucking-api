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
            $table->string('nobon', 50)->default('');
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
