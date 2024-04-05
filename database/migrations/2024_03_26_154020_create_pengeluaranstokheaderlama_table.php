<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePengeluaranstokheaderlamaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pengeluaranstokheaderlama', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->date('tglbukti',50)->nullable();
            $table->longText('keterangan')->nullable();            
            $table->unsignedBigInteger('pengeluaranstok_id')->nullable();
            $table->unsignedBigInteger('trado_id')->nullable();
            $table->string('trado',500)->nullable();
            $table->unsignedBigInteger('gudang_id')->nullable();
            $table->string('gudang',500)->nullable();
            $table->unsignedBigInteger('gandengan_id')->nullable();
            $table->string('gandengan',500)->nullable();
            $table->unsignedBigInteger('supir_id')->nullable();
            $table->string('supir',500)->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->string('supplier',500)->nullable();
            $table->string('pengeluaranstok_nobukti',50)->nullable();
            $table->string('penerimaanstok_nobukti',50)->nullable();
            $table->string('pengeluarantrucking_nobukti',50)->nullable();
            $table->string('servicein_nobukti',50)->nullable();
            $table->unsignedBigInteger('kerusakan_id')->nullable();
            $table->string('kerusakan',500)->nullable();
            $table->integer('statuspotongretur')->Length(11)->nullable();
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->string('bank',500)->nullable();
            $table->string('penerimaan_nobukti',50)->nullable();
            $table->string('coa',50)->nullable();
            $table->string('postingdari',50)->nullable();
            $table->date('tglkasmasuk')->nullable();
            $table->string('hutangbayar_nobukti',50)->nullable();
            $table->unsignedBigInteger('statusformat')->nullable();  
            $table->integer('statuscetak')->Length(11)->nullable();
            $table->string('userbukacetak',50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->Length(11)->nullable();                
            $table->integer('statusapprovaledit')->Length(11)->nullable();
            $table->string('userapprovaledit',50)->nullable();
            $table->date('tglapprovaledit')->nullable();
            $table->dateTime('tglbatasedit')->nullable();            
            $table->integer('statusapprovaleditketerangan')->Length(11)->nullable();
            $table->string('userapprovaleditketerangan', 50)->nullable();
            $table->date('tglapprovaleditketerangan')->nullable();
            $table->dateTime('tglbataseditketerangan')->nullable();            
            $table->integer('statuskirimberkas')->Length(11)->nullable();
            $table->string('userkirimberkas',50)->nullable();
            $table->date('tglkirimberkas')->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby',50)->nullable();
            $table->string('editing_by',50)->nullable();            
            $table->dateTime('editing_at')->nullable();                 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pengeluaranstokheaderlama');
    }
}
