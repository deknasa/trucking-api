<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupirTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supir', function (Blueprint $table) {
            $table->id();
            $table->string('nsupir', 100)->default('');
            $table->string('alamat', 100)->default('');
            $table->string('kota', 100)->default('');
            $table->string('telp', 30)->default('');
            $table->integer('statusaktif')->length(11)->default(0);
            $table->double('nomdepositsa', 15,2)->default(0);
            $table->BigInteger('depositke')->default(0);
            $table->date('tgl')->default('1900/1/1');
            $table->double('nompinjamansa', 15,2)->default(0);
            $table->string('modifiedby', 30)->default('');
            $table->unsignedBigInteger('supirold_id')->default(0);
            $table->date('tglexpsim')->default('1900/1/1');
            $table->string('nosim', 30)->default('');
            $table->string('keterangan', 100)->default('');
            $table->string('noktp', 30)->default('');
            $table->string('nokk', 30)->default('');
            $table->integer('statusadaupdategambar')->length(11)->default(0);
            $table->integer('statuslluarkota')->length(11)->default(0);
            $table->integer('statuszonatertentu]')->length(11)->default(0);
            $table->integer('zona]')->length(11)->default(0);
            $table->double('angsuranpjt', 15,2)->default(0);
            $table->double('plafondeposito', 15,2)->default(0);
            $table->string('photosupir', 100)->default('');
            $table->string('photoktp', 100)->default('');
            $table->string('photosim', 100)->default('');
            $table->string('photokk', 100)->default('');
            $table->string('photoskck', 100)->default('');
            $table->string('photodomisili', 100)->default('');
            $table->string('ketresign', 500)->default('');
            $table->integer('statuspameran')->length(11)->default(0);
            $table->integer('statusbacklist')->length(11)->default(0);
            $table->date('tglberhentisupir')->default('1900/1/1');
            $table->date('tgllahir')->default('1900/1/1');
            $table->date('tglterbitsim')->default('1900/1/1');
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
        Schema::dropIfExists('supir');
    }
}
