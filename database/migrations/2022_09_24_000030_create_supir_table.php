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
            $table->string('namasupir', 100)->default('');
            $table->string('alamat', 100)->default('');
            $table->string('kota', 100)->default('');
            $table->string('telp', 30)->default('');
            $table->integer('statusaktif')->length(11)->default(0);
            $table->string('pemutihansupir_nobukti', 50)->default('');
            $table->double('nominaldepositsa', 15,2)->default(0);
            $table->BigInteger('depositke')->default(0);
            $table->date('tglmasuk')->default('1900/1/1');
            $table->double('nominalpinjamansaldoawal', 15,2)->default(0);
            $table->unsignedBigInteger('supirold_id')->default(0);
            $table->date('tglexpsim')->default('1900/1/1');
            $table->string('nosim', 30)->default('');
            $table->longText('keterangan')->default('');
            $table->string('noktp', 30)->default('');
            $table->string('nokk', 30)->default('');
            $table->integer('statusadaupdategambar')->length(11)->default(0);
            $table->string('statusluarkota')->default(0);
            $table->integer('statuszonatertentu')->length(11)->default(0);
            $table->unsignedBigInteger('zona_id')->default(0);
            $table->double('angsuranpinjaman', 15,2)->default(0);
            $table->double('plafondeposito', 15,2)->default(0);
            $table->string('photosupir', 4000)->default('');
            $table->string('photoktp', 4000)->default('');
            $table->string('photosim', 4000)->default('');
            $table->string('photokk', 4000)->default('');
            $table->string('photoskck', 4000)->default('');
            $table->string('photodomisili', 4000)->default('');
            $table->string('photovaksin', 4000)->default('');
            $table->string('pdfsuratperjanjian', 4000)->default('');
            $table->longText('keteranganresign')->default('');
            $table->integer('statusblacklist')->length(11)->default(0);
            $table->date('tglberhentisupir')->default('1900/1/1');
            $table->date('tgllahir')->default('1900/1/1');
            $table->date('tglterbitsim')->default('1900/1/1');
            $table->string('modifiedby', 30)->default('');
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
