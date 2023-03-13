<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateSaldohutangTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('saldohutang', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti', 50)->unique();            
            $table->date('tglbukti')->default('1900/1/1');            
            $table->string('coa',50)->default('');            
            $table->double('total',15,2)->default(0);            
            $table->string('postingdari', 50)->default('');            
            $table->unsignedBigInteger('pelanggan_id')->default('0');
            $table->unsignedBigInteger('supplier_id')->default(0);            
            $table->unsignedBigInteger('statusformat')->default(0);  
            $table->integer('statuscetak')->Length(11)->default('0');
            $table->string('userbukacetak',50)->default('');
            $table->date('tglbukacetak')->default('1900/1/1');
            $table->integer('jumlahcetak')->Length(11)->default('0');
            $table->string('modifiedby', 50)->default('');              
            $table->timestamps();

            $table->foreign('coa', 'saldohutang_akunpusat_coa_foreign')->references('coa')->on('akunpusat');    
            $table->foreign('pelanggan_id', 'saldohutang_pelanggan_pelanggan_id_foreign')->references('id')->on('pelanggan');    
            $table->foreign('supplier_id', 'saldohutang_supplier_supplier_id_foreign')->references('id')->on('supplier');    

        });

        
        DB::statement("ALTER TABLE saldohutang NOCHECK CONSTRAINT saldohutang_akunpusat_coa_foreign");
        DB::statement("ALTER TABLE saldohutang NOCHECK CONSTRAINT saldohutang_pelanggan_pelanggan_id_foreign");
        DB::statement("ALTER TABLE saldohutang NOCHECK CONSTRAINT saldohutang_supplier_supplier_id_foreign");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('saldohutang');
    }
}
