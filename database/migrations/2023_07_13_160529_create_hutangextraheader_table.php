<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateHutangextraheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hutangextraheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti', 50)->unique();            
            $table->date('tglbukti')->nullable();            
            $table->longText('keterangan')->nullable();            
            $table->string('coa',50)->nullable();            
            $table->string('coakredit',50)->nullable();            
            $table->double('total',15,2)->nullable();            
            $table->string('postingdari', 50)->nullable();   
            $table->string('hutang_nobukti', 50)->nullable();                     
            $table->unsignedBigInteger('pelanggan_id')->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();            
            $table->unsignedBigInteger('statusformat')->nullable();  
            $table->integer('statuscetak')->Length(11)->nullable();
            $table->string('userbukacetak',50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->Length(11)->nullable();
            $table->integer('statusapproval')->Length(11)->nullable();
            $table->date('tglapproval')->nullable();
            $table->string('userapproval',50)->nullable();
            $table->integer('statuskirimberkas')->Length(11)->nullable();
            $table->string('userkirimberkas',50)->nullable();
            $table->date('tglkirimberkas')->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby', 50)->nullable();                        
            $table->string('editing_by',50)->nullable();            
            $table->dateTime('editing_at')->nullable();              
            $table->timestamps();

            $table->foreign('coa', 'hutangextraheader_akunpusat_coa_foreign')->references('coa')->on('akunpusat');    
            $table->foreign('pelanggan_id', 'hutangextraheader_pelanggan_pelanggan_id_foreign')->references('id')->on('pelanggan');    
            $table->foreign('supplier_id', 'hutangextraheader_supplier_supplier_id_foreign')->references('id')->on('supplier');    

        });

        DB::statement("ALTER TABLE hutangextraheader NOCHECK CONSTRAINT hutangextraheader_pelanggan_pelanggan_id_foreign");
        DB::statement("ALTER TABLE hutangextraheader NOCHECK CONSTRAINT hutangextraheader_akunpusat_coa_foreign");
        DB::statement("ALTER TABLE hutangextraheader NOCHECK CONSTRAINT hutangextraheader_supplier_supplier_id_foreign");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hutangextraheader');
    }
}
