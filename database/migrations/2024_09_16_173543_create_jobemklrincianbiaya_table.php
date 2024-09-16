<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateJobemklrincianbiayaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jobemklrincianbiaya', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('jobemkl_id')->nullable();
            $table->string('nobukti',50)->nullable();
            $table->unsignedBigInteger('biayaemkl_id')->nullable();
            $table->double('nominal', 15,2)->nullable();
            $table->longText('keterangan')->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby',50)->nullable();
            $table->timestamps();

            $table->foreign('jobemkl_id', 'jobemklrincianbiaya_jobemkl_jobemkl_id_foreign')->references('id')->on('jobemkl')->onDelete('cascade');       
            // $table->foreign('biayaemkl_id', 'jobemklrincianbiaya_biayaemkl_biayaemkl_id_foreign')->references('id')->on('biayaemkl');


        });
        // DB::statement("ALTER TABLE biayaemkl NOCHECK CONSTRAINT jobemklrincianbiaya_biayaemkl_biayaemkl_id_foreign");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('jobemklrincianbiaya');
    }
}
