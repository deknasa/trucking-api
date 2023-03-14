<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePencairangiropengeluaranheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::dropIfExists('pencairangiropengeluaranheader');
        
        Schema::create('pencairangiropengeluaranheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->date('tglbukti')->default('1900/1/1');
            $table->longText('keterangan')->default('');            
            $table->string('pengeluaran_nobukti',50)->default('');
            $table->integer('statusapproval')->Length(11)->default('0');
            $table->string('userapproval',50)->default('');
            $table->date('tglapproval')->default('1900/1/1');
            $table->unsignedBigInteger('statusformat')->default(0);
            $table->string('modifiedby',50)->default('');            
            $table->timestamps();

            $table->foreign('pengeluaran_nobukti', 'pencairangiropengeluaranheader_pengeluaranheader_nobukti_foreign')->references('nobukti')->on('pengeluaranheader');
        });

        DB::statement("ALTER TABLE pencairangiropengeluaranheader NOCHECK CONSTRAINT pencairangiropengeluaranheader_pengeluaranheader_nobukti_foreign");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pencairangiropengeluaranheader');
    }
}
