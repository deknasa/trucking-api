<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateBiayaextrasupirheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('biayaextrasupirheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti', 50)->unique();
            $table->date('tglbukti')->nullable();
            $table->string('suratpengantar_nobukti', 50)->nullable();
            $table->string('editing_by',50)->nullable();            
            $table->dateTime('editing_at')->nullable();                  
            $table->longText('info')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->timestamps();

            $table->foreign('suratpengantar_nobukti', 'biayaextrasupirheader_suratpengantar_suratpengantar_nobukti_foreign')->references('nobukti')->on('suratpengantar');

        });

        DB::statement("ALTER TABLE biayaextrasupirheader NOCHECK CONSTRAINT biayaextrasupirheader_suratpengantar_suratpengantar_nobukti_foreign");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('biayaextrasupirheader');
    }
}
