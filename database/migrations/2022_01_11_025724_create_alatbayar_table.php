<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatealatbayarTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alatbayar', function (Blueprint $table) {
            $table->id();
            $table->string('namaalatbayar', 50)->default('');
            $table->integer('lnsgcair')->length(11)->default(0);
            $table->integer('def')->length(11)->default(0);
            $table->unsignedBigInteger('bank_id')->default(0);
            $table->string('modifiedby', 50)->default('');
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
        Schema::dropIfExists('alatbayar');
    }
}
