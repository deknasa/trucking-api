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
            $table->date('tglbukti')->nullable();
            $table->integer('statusritasi')->nullable();
            $table->string('suratpengantar_nobukti',50)->nullable();
            $table->integer('suratpengantar_urutke')->length(11)->nullable();
            $table->unsignedBigInteger('supir_id')->nullable();
            $table->unsignedBigInteger('dataritasi_id')->nullable();
            $table->unsignedBigInteger('trado_id')->nullable();
            $table->double('jarak',15,2)->nullable();
            $table->double('upah',15,2)->nullable();
            $table->double('extra',15,2)->nullable();
            $table->double('gaji',15,2)->nullable();
            $table->unsignedBigInteger('dari_id')->nullable();
            $table->unsignedBigInteger('sampai_id')->nullable();
            $table->unsignedBigInteger('statusformat')->nullable(); 
            $table->string('editing_by',50)->nullable();            
            $table->dateTime('editing_at')->nullable();  
            $table->longText('info')->nullable();
            $table->string('modifiedby',50)->nullable();
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
