<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSaldoabsensisupirheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('saldoabsensisupirheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti', 50)->unique();
            $table->date('tglbukti')->nullable();
            $table->longText('keterangan')->nullable();
            $table->string('kasgantung_nobukti', 50)->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->unsignedBigInteger('statusformat')->nullable();
            $table->integer('statuscetak')->Length(11)->nullable();
            $table->string('userbukacetak', 50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->Length(11)->nullable();
            $table->integer('statusapprovaleditabsensi')->Length(11)->nullable();
            $table->string('userapprovaleditabsensi', 50)->nullable();
            $table->date('tglapprovaleditabsensi')->nullable();
            $table->dateTime('tglbataseditabsensi')->nullable();
            $table->integer('statusapprovalpengajuantripinap')->Length(11)->nullable();
            $table->string('userapprovalpengajuantripinap', 50)->nullable();
            $table->date('tglapprovalpengajuantripinap')->nullable();
            $table->dateTime('tglbataspengajuantripinap')->nullable();
            $table->integer('statusapprovalfinalabsensi')->Length(11)->nullable();
            $table->string('userapprovalfinalabsensi', 50)->nullable();
            $table->datetime('tglapprovalfinalabsensi')->nullable();
            $table->integer('statuskirimberkas')->Length(11)->nullable();
            $table->string('userkirimberkas', 50)->nullable();
            $table->date('tglkirimberkas')->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->string('editing_by', 50)->nullable();
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
        Schema::dropIfExists('saldoabsensisupirheader');
    }
}
