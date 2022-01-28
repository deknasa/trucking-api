<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHutangbayarheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hutangbayarheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti', 50)->unique();            
            $table->date('tgl')->default('1900/1/1');            
            $table->longText('keterangan')->default('');            
            $table->unsignedBigInteger('bank_id')->default('0');            
            $table->unsignedBigInteger('supplier_id')->default('0');            
            $table->string('pengeluaran_nobukti', 50)->default('');            
            $table->unsignedBigInteger('coa_id')->default('0');            
            $table->integer('statusapproval')->length(11)->default('0');            
            $table->date('tglapproval')->default('1900/1/1');            
            $table->string('userapproval', 50)->default('');            
            $table->string('modifiedby', 50)->default('');            
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
        Schema::dropIfExists('hutangbayarheader');
    }
}
