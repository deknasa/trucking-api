<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateProsesgajisupirdetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('prosesgajisupirdetail');

        Schema::create('prosesgajisupirdetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prosesgajisupir_id')->default('0');
            $table->string('nobukti',50)->default('');
            $table->string('gajisupir_nobukti',50)->default('');
            $table->unsignedBigInteger('supir_id')->default('0');
            $table->unsignedBigInteger('trado_id')->default('0');
            $table->double('nominal',15,2)->default('0');
            $table->longText('keterangan')->default('');
            $table->string('modifiedby',50)->default('');
            $table->timestamps();


            $table->foreign('prosesgajisupir_id', 'prosesgajisupirdetail_prosesgajisupirheader_prosesgajisupir_id_foreign')->references('id')->on('prosesgajisupirheader')->onDelete('cascade');    
            $table->foreign('gajisupir_nobukti', 'prosesgajisupirdetail_gajisupirheader_gajisupir_nobukti_foreign')->references('nobukti')->on('gajisupirheader');
            $table->foreign('supir_id', 'prosesgajisupirdetail_supir_supir_id_foreign')->references('id')->on('supir');
            $table->foreign('trado_id', 'prosesgajisupirdetail_trado_trado_id_foreign')->references('id')->on('trado');

        });

        DB::statement("ALTER TABLE prosesgajisupirdetail NOCHECK CONSTRAINT prosesgajisupirdetail_gajisupirheader_gajisupir_nobukti_foreign");
        DB::statement("ALTER TABLE prosesgajisupirdetail NOCHECK CONSTRAINT prosesgajisupirdetail_supir_supir_id_foreign");
        DB::statement("ALTER TABLE prosesgajisupirdetail NOCHECK CONSTRAINT prosesgajisupirdetail_trado_trado_id_foreign");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prosesgajisupirdetail');
    }
}
