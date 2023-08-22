<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOpnamedetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('opnamedetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('opname_id')->nullable();
            $table->string('nobukti',50)->nullable();
            $table->unsignedBigInteger('stok_id')->nullable();
            $table->double('qty',15,2)->nullable();
            $table->double('qtyfisik',15,2)->nullable();
            $table->string('modifiedby',50)->nullable();
            $table->timestamps();

            $table->foreign('opname_id', 'opnamedetail_opnameheader_opname_id_foreign')->references('id')->on('opnameheader')->onDelete('cascade');    
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('opnamedetail');
    }
}
