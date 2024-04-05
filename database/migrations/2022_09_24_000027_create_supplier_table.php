<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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

        Schema::dropIfExists('supplier');

        Schema::create('supplier', function (Blueprint $table) {
            $table->id();
            $table->longText('namasupplier')->nullable();
            $table->string('namakontak', 150)->nullable();
            $table->longText('alamat')->nullable();
            $table->string('kota', 150)->nullable();
            $table->string('kodepos', 50)->nullable();
            $table->string('notelp1', 50)->nullable();
            $table->string('notelp2', 50)->nullable();
            $table->string('email', 50)->nullable();
            $table->integer('statusaktif')->length(11)->nullable();
            $table->string('web', 50)->nullable();
            $table->string('namapemilik', 150)->nullable();
            $table->string('jenisusaha', 150)->nullable();
            $table->string('bank', 150)->nullable();
            $table->string('rekeningbank', 150)->nullable();
            $table->string('namarekening', 150)->nullable();
            $table->string('jabatan', 150)->nullable();
            $table->integer('statusdaftarharga')->length(11)->nullable();
            $table->integer('statuspostingtnl')->length(11)->nullable();
            $table->string('kategoriusaha', 150)->nullable();
            $table->double('top', 15, 2)->nullable();
            $table->longText('keterangan')->nullable();
            $table->integer('statusapproval')->Length(11)->nullable();
            $table->date('tglapproval')->nullable();
            $table->string('userapproval', 50)->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->unsignedBigInteger('tas_id')->nullable();
            $table->string('editing_by',50)->nullable();            
            $table->dateTime('editing_at')->nullable();             
            $table->string('coa', 50)->nullable();
            $table->longText('info')->nullable();
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
