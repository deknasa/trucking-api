<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBukapengeluaranstokTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bukapengeluaranstok', function (Blueprint $table) {
            $table->id();
            $table->date('tglbukti')->nullable();
            $table->dateTime('tglbatas')->nullable();
            $table->unsignedBigInteger('pengeluaranstok_id')->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby', 50)->nullable();                  
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
        Schema::dropIfExists('bukapengeluaranstok');
    }
}
