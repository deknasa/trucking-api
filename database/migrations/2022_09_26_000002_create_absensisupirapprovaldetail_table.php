<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateAbsensisupirapprovaldetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::dropIfExists('absensisupirapprovaldetail');
        
        Schema::create('absensisupirapprovaldetail', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->nullable();
            $table->unsignedBigInteger('absensisupirapproval_id')->nullable();
            $table->unsignedBigInteger('trado_id')->nullable();
            $table->unsignedBigInteger('supir_id')->nullable();
            $table->unsignedBigInteger('supirserap_id')->nullable();
            $table->integer('statusjeniskendaraan')->Length(11)->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby', 200)->nullable();
            $table->timestamps();


            $table->foreign('absensisupirapproval_id', 'absensisupirapprovaldetail_absensisupirheader_absensi_id_foreign')->references('id')->on('absensisupirapprovalheader')->onDelete('cascade');    
            $table->foreign('trado_id', 'absensisupirapprovaldetail_trado_trado_id_foreign')->references('id')->on('trado');
            $table->foreign('supir_id', 'absensisupirapprovaldetail_supir_supir_id_foreign')->references('id')->on('supir');
            $table->foreign('supirserap_id', 'absensisupirapprovaldetail_supir_supirserap_id_foreign')->references('id')->on('supir');

        });

        DB::statement("ALTER TABLE absensisupirapprovaldetail NOCHECK CONSTRAINT absensisupirapprovaldetail_trado_trado_id_foreign");
        DB::statement("ALTER TABLE absensisupirapprovaldetail NOCHECK CONSTRAINT absensisupirapprovaldetail_supir_supir_id_foreign");
        DB::statement("ALTER TABLE absensisupirapprovaldetail NOCHECK CONSTRAINT absensisupirapprovaldetail_supir_supirserap_id_foreign");

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
