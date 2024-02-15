<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatealatbayarTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::dropIfExists('alatbayar');
        
        Schema::create('alatbayar', function (Blueprint $table) {
            $table->id();
            $table->string('kodealatbayar', 50)->nullable();
            $table->string('namaalatbayar', 50)->nullable();
            $table->longtext('keterangan')->nullable();
            $table->integer('statuslangsungcair')->length(11)->nullable();
            $table->integer('statusdefault')->length(11)->nullable();
            $table->string('coa', 50)->nullable();
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->integer('statusaktif')->length(11)->nullable();                
            $table->string('tipe', 50)->nullable();
            $table->longText('info')->nullable();
            $table->unsignedBigInteger('tas_id')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->timestamps();

            $table->foreign('bank_id', 'alatbayar_bank_bank_id_foreign')->references('id')->on('bank');

        });

        
        DB::statement("ALTER TABLE alatbayar NOCHECK CONSTRAINT alatbayar_bank_bank_id_foreign");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('alatbayar');
    }
}
