<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePelangganTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()

    
    {

        Schema::dropIfExists('pelanggan');
        
        Schema::create('pelanggan', function (Blueprint $table) {
            $table->id();
            $table->string('kodepelanggan',50)->nullable();
            $table->string('namapelanggan',100)->nullable();
            $table->string('namakontak',1000)->nullable();
            $table->longText('keterangan')->nullable();
            $table->string('telp',100)->nullable();
            $table->string('alamat',200)->nullable();
            $table->string('alamat2',200)->nullable();
            $table->string('kota',200)->nullable();
            $table->string('kodepos',50)->nullable();


            $table->string('npwp',50)->nullable();
            $table->string('noktp',50)->nullable();
            $table->string('alamatfakturpajak',100)->nullable();
            $table->string('alamatkantorpenagihan',100)->nullable();
            $table->string('namapemilik',100)->nullable();
            $table->string('telpkantor',100)->nullable();
            $table->string('faxkantor',100)->nullable();
            $table->string('website',100)->nullable();
            $table->string('contactperson',100)->nullable();
            $table->string('telpcp',100)->nullable();
            $table->string('asuransitas',100)->nullable();
            $table->string('asuransisendiri',100)->nullable();
            $table->double('top', 15,2)->nullable();
            $table->string('prosedurpenagihan',100)->nullable();
            $table->string('syaratpenagihan',100)->nullable();
            $table->string('pickeuangan',100)->nullable();
            $table->string('telppickeuangan',50)->nullable();
            $table->string('jenisusaha',100)->nullable();
            $table->string('volumeperbulan',100)->nullable();
            $table->string('kompetitor',100)->nullable();
            $table->string('referensi',100)->nullable();
            $table->double('nominalplafon', 15,2)->nullable();
            $table->string('danaditransferdari',100)->nullable();
            $table->string('atasnama',100)->nullable();
            $table->string('norekening',100)->nullable();
            $table->string('bank',100)->nullable();


            $table->integer('statusaktif')->length(11)->nullable();                
            $table->unsignedBigInteger('tas_id')->nullable();
            $table->string('editing_by',50)->nullable();            
            $table->dateTime('editing_at')->nullable();            

            $table->longText('info')->nullable();
            $table->string('modifiedby',50)->nullable();
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
        Schema::dropIfExists('pelanggan');
    }
}
