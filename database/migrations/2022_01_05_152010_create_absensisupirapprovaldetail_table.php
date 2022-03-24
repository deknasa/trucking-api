<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAbsensisupirapprovaldetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('absensisupirapprovaldetail', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->default('');
            $table->unsignedBigInteger('absensisupirapproval_id')->default(0);
            $table->unsignedBigInteger('trado_id')->default(0);
            $table->unsignedBigInteger('supir_id')->default(0);
            $table->unsignedBigInteger('supirserap_id')->default(0);
            $table->string('modifiedby', 200)->default('');
            $table->timestamps();

            $table->foreign('trado_id')->references('id')->on('trado');
            $table->foreign('supir_id')->references('id')->on('supir');
            $table->foreign('supirserap_id')->references('id')->on('supir');
            $table->foreign('absensisupirapproval_id')->references('id')->on('absensisupirapprovalheader')->onDelete('cascade');             

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('absensisupirapprovaldetail');
    }
}
