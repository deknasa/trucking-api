<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateRitasiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('ritasi');
        
        Schema::create('ritasi', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->date('tglbukti')->default('1900/1/1');
            $table->integer('statusritasi')->default(0);
            $table->string('suratpengantar_nobukti',50)->default('');
            $table->unsignedBigInteger('supir_id')->default(0);
            $table->unsignedBigInteger('trado_id')->default(0);
            $table->double('jarak',15,2)->default(0);
            $table->double('gaji',15,2)->default(0);
            $table->unsignedBigInteger('dari_id')->default(0);
            $table->unsignedBigInteger('sampai_id')->default(0);
            $table->unsignedBigInteger('statusformat')->default(0); 
            $table->string('modifiedby',50)->default('');
            $table->timestamps();


            $table->foreign('dari_id', 'ritasi_kota_dari_id_foreign')->references('id')->on('kota');  
            $table->foreign('sampai_id', 'ritasi_kota_sampai_id_foreign')->references('id')->on('kota');  
            $table->foreign('trado_id', 'ritasi_trado_trado_id_foreign')->references('id')->on('trado');  
            $table->foreign('supir_id', 'ritasi_supir_supir_id_foreign')->references('id')->on('supir');  
            $table->foreign('suratpengantar_nobukti', 'ritasi_suratpengantar_suratpengantar_nobuktiforeign')->references('nobukti')->on('suratpengantar');  


        });

        DB::statement("ALTER TABLE ritasi NOCHECK CONSTRAINT ritasi_kota_dari_id_foreign");
        DB::statement("ALTER TABLE ritasi NOCHECK CONSTRAINT ritasi_kota_sampai_id_foreign");
        DB::statement("ALTER TABLE ritasi NOCHECK CONSTRAINT ritasi_trado_trado_id_foreign");
        DB::statement("ALTER TABLE ritasi NOCHECK CONSTRAINT ritasi_supir_supir_id_foreign");
        DB::statement("ALTER TABLE ritasi NOCHECK CONSTRAINT ritasi_suratpengantar_suratpengantar_nobuktiforeign");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ritasi');
    }
}
