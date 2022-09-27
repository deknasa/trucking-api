<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatuscontainerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::dropIfExists('statuscontainer');
        
        Schema::create('statuscontainer', function (Blueprint $table) {
            $table->id();
            $table->string('kodestatuscontainer',50)->default('');
            $table->longText('keterangan')->default('');
            $table->integer('statusaktif')->length(11)->default('');
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
        Schema::dropIfExists('statuscontainer');
    }
}
