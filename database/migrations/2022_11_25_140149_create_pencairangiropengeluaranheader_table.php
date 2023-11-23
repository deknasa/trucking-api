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
            $table->date('tglbukti')->nullable();
            $table->longText('keterangan')->nullable();            
            $table->string('pengeluaran_nobukti',50)->nullable();
            $table->integer('statusapproval')->Length(11)->nullable();
            $table->string('userapproval',50)->nullable();
            $table->date('tglapproval')->nullable();
            $table->unsignedBigInteger('statusformat')->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby',50)->nullable();            
            $table->string('editing_by',50)->nullable();            
            $table->dateTime('editing_at')->nullable();            
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
