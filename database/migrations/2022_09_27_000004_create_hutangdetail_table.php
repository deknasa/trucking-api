<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateHutangdetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('hutangdetail');

        Schema::create('hutangdetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hutang_id')->default(0);            
            $table->string('nobukti', 50)->default('');            
            $table->unsignedBigInteger('supplier_id')->default(0);            
            $table->date('tgljatuhtempo')->default('1900/1/1');            
            $table->double('total',15,2)->default('0');            
            $table->double('cicilan',15,2)->default('0');            
            $table->longText('keterangan')->default('');            
            $table->double('totalbayar',15,2)->default('0');            
            $table->string('modifiedby', 50)->default('');            
            $table->timestamps();


            $table->foreign('hutang_id', 'hutangdetail_hutangheader_hutang_id_foreign')->references('id')->on('hutangheader')->onDelete('cascade');    
            $table->foreign('hutang_id', 'hutangdetail_supplier_supplier_id_foreign')->references('id')->on('supplier');    

        });
        DB::statement("ALTER TABLE hutangdetail NOCHECK CONSTRAINT hutangdetail_supplier_supplier_id_foreign");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hutangdetail');
    }
}
