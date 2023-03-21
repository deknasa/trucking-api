<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateServiceindetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('serviceindetail');

        Schema::create('serviceindetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('servicein_id')->nullable();
            $table->string('nobukti',50)->nullable();
            $table->unsignedBigInteger('mekanik_id')->nullable();
            $table->longText('keterangan')->nullable();
            $table->string('modifiedby',50)->nullable();
            $table->timestamps();

            $table->foreign('servicein_id', 'serviceindetail_serviceinheader_servicein_id_foreign')->references('id')->on('serviceinheader')->onDelete('cascade');    
            $table->foreign('mekanik_id', 'serviceindetail_mekanik_mekanik_id_foreign')->references('id')->on('mekanik');


        });
        DB::statement("ALTER TABLE serviceindetail NOCHECK CONSTRAINT serviceindetail_mekanik_mekanik_id_foreign");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('serviceindetail');
    }
}
