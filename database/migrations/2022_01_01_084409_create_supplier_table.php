<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupplierTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supplier', function (Blueprint $table) {
            $table->id();
            $table->longText('namasupplier')->default('');            
            $table->string('namakontak',150)->default('');            
            $table->longText('alamat')->default('');            
            $table->unsignedBigInteger('coa_id')->default('0');            
            $table->string('kota',150)->default('');            
            $table->string('kodepos',50)->default('');            
            $table->string('notelp1',50)->default('');            
            $table->string('notelp2',50)->default('');            
            $table->string('email',50)->default('');            
            $table->integer('statussupllier')->length(11)->default('0');            
            $table->string('web',50)->default('');            
            $table->string('namapemilik',150)->default('');            
            $table->string('jenisusaha',150)->default('');            
            $table->integer('top')->length(11)->default('0');            
            $table->string('bank',150)->default('');            
            $table->string('rekeningbank',150)->default('');            
            $table->string('namabank',150)->default('');            
            $table->string('jabatan',150)->default('');            
            $table->integer('statusdaftarharga')->length(11)->default('0');            
            $table->string('kategoriusaha',150)->default('');            
            $table->decimal('bataskredit',12,2)->default('');            
            $table->string('modifiedby',50)->default('');            
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
        Schema::dropIfExists('supplier');
    }
}
