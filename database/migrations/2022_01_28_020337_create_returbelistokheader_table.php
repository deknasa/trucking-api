<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReturbelistokheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('returbelistokheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->date('tgl')->default('1900/1/1');
            $table->string('belistok_nobukti',50)->default('');
            $table->unsignedBigInteger('supplier_id')->default('0');
            $table->double('persentasediscount',15,2)->default('0');
            $table->double('nominaldiscount',15,2)->default('0');
            $table->double('persentaseppn',15,2)->default('0');
            $table->double('nominalppn',15,2)->default('0');
            $table->double('total',15,2)->default('0');
            $table->longText('keterangan')->default('');
            $table->date('tgljt')->default('1900/1/1');
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
        Schema::dropIfExists('returbelistokheader');
    }
}
