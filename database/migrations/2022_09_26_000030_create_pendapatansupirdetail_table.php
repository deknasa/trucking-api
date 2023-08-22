<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePendapatansupirdetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('pendapatansupirdetail');

        Schema::create('pendapatansupirdetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pendapatansupir_id')->nullable();
            $table->string('nobukti',50)->nullable();
            $table->unsignedBigInteger('supir_id')->nullable();
            $table->string('nobuktitrip',50)->nullable();
            $table->string('nobuktirincian',50)->nullable();
            $table->Integer('dari_id')->nullable();
            $table->Integer('sampai_id')->nullable();
            $table->double('nominal',15,2)->nullable();
            $table->double('gajikenek',15,2)->nullable();
            $table->longText('keterangan')->nullable();
            $table->string('modifiedby',50)->nullable();            
            $table->timestamps();

            $table->foreign('pendapatansupir_id', 'pendapatansupirdetail_pendapatansupirheader_pendapatansupir_id_foreign')->references('id')->on('pendapatansupirheader')->onDelete('cascade');    
            $table->foreign('supir_id', 'pendapatansupirdetail_supir_supir_id_foreign')->references('id')->on('supir');    


        });
        DB::statement("ALTER TABLE pendapatansupirdetail NOCHECK CONSTRAINT pendapatansupirdetail_supir_supir_id_foreign");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pendapatansupirdetail');
    }
}
