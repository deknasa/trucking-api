<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQtytambahgantioliTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('qtytambahgantioli', function (Blueprint $table) {
            $table->id();
            $table->double('qty', 15, 2)->nullable();
            $table->integer('statusoli')->length(11)->nullable();
            $table->string('editing_by', 50)->nullable();
            $table->dateTime('editing_at')->nullable();
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
        Schema::dropIfExists('qtytambahgantioli');
    }
}
