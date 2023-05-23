<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApprovaltradogambarTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('approvaltradogambar', function (Blueprint $table) {
            $table->id();
            $table->string('kodetrado', 30)->nullable();
            $table->integer('statusapproval')->length(11)->nullable();
            $table->date('tglbatas')->nullable();
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
        Schema::dropIfExists('approvaltradogambar');
    }
}
