<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBukapenerimaanstokTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bukapenerimaanstok', function (Blueprint $table) {
            $table->id();
            $table->date('tglbukti')->nullable();
            $table->dateTime('tglbatas')->nullable();
            $table->unsignedBigInteger('penerimaanstok_id')->nullable();
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
        Schema::dropIfExists('bukapenerimaanstok');
    }
}
