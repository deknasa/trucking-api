<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateSupirTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('supir');

        Schema::create('supir', function (Blueprint $table) {
            $table->id();
            $table->string('namasupir', 100)->nullable();
            $table->string('namaalias', 100)->nullable();
            $table->string('alamat', 100)->nullable();
            $table->string('kota', 100)->nullable();
            $table->string('telp', 30)->nullable();
            $table->integer('statusaktif')->length(11)->nullable();
            $table->string('pemutihansupir_nobukti', 50)->nullable();
            $table->double('nominaldepositsa', 15,2)->nullable();
            $table->BigInteger('depositke')->nullable();
            $table->date('tglmasuk')->nullable();
            $table->double('nominalpinjamansaldoawal', 15,2)->nullable();
            $table->unsignedBigInteger('supirold_id')->nullable();
            $table->date('tglexpsim')->nullable();
            $table->string('nosim', 30)->nullable();
            $table->longText('keterangan')->nullable();
            $table->string('noktp', 30)->nullable();
            $table->string('nokk', 30)->nullable();
            $table->integer('statusadaupdategambar')->length(11)->nullable();
            $table->string('statusluarkota')->nullable();
            $table->date('tglbatastidakbolehluarkota')->nullable();
            $table->integer('statuszonatertentu')->length(11)->nullable();
            $table->unsignedBigInteger('zona_id')->nullable();
            $table->double('angsuranpinjaman', 15,2)->nullable();
            $table->double('plafondeposito', 15,2)->nullable();
            $table->string('photosupir', 4000)->nullable();
            $table->string('photoktp', 4000)->nullable();
            $table->string('photosim', 4000)->nullable();
            $table->string('photokk', 4000)->nullable();
            $table->string('photoskck', 4000)->nullable();
            $table->string('photodomisili', 4000)->nullable();
            $table->string('photovaksin', 4000)->nullable();
            $table->string('pdfsuratperjanjian', 4000)->nullable();
            $table->longText('keteranganresign')->nullable();
            $table->integer('statusblacklist')->length(11)->nullable();
            $table->date('tglberhentisupir')->nullable();
            $table->longText('keteranganberhentisupir')->nullable();
            $table->date('tgllahir')->nullable();
            $table->date('tglterbitsim')->nullable();
            $table->unsignedBigInteger('mandor_id')->nullable();
            $table->integer('statuspostingtnl')->length(11)->nullable();
            $table->dateTime('tglberlakumilikmandor')->nullable();            
            $table->unsignedBigInteger('tas_id')->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby', 30)->nullable();
            $table->timestamps();

            $table->foreign('zona_id', 'supir_zona_zona_id_foreign')->references('id')->on('zona');
            $table->foreign('supirold_id', 'supir_supir_supirold_id_foreign')->references('id')->on('supir');
        });

        DB::statement("ALTER TABLE supir NOCHECK CONSTRAINT supir_zona_zona_id_foreign");
        DB::statement("ALTER TABLE supir NOCHECK CONSTRAINT supir_supir_supirold_id_foreign");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('supir');
    }
}
