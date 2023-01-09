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
            $table->string('kodealatbayar', 50)->default('');
            $table->string('namaalatbayar', 50)->default('');
            $table->longtext('keterangan')->default('');
            $table->integer('statuslangsungcair')->length(11)->default(0);
            $table->integer('statusdefault')->length(11)->default(0);
            $table->string('coa', 50)->default('');
            $table->unsignedBigInteger('bank_id')->default(0);
            $table->string('modifiedby', 50)->default('');
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
