<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePemutihanSupirHeaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pemutihansupirheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti', 50)->unique();
            $table->date('tglbukti')->nullable();
            $table->unsignedBigInteger('supir_id')->nullable();
            $table->double('pengeluaransupir', 15, 2)->nullable();
            $table->double('penerimaansupir', 15, 2)->nullable();
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->string('coa', 50)->nullable();
            $table->string('penerimaan_nobukti', 50)->nullable();
            $table->string('pengeluaran_nobukti', 50)->nullable();
            $table->string('penerimaantruckingposting_nobukti', 50)->nullable();
            $table->string('penerimaantruckingnonposting_nobukti', 50)->nullable();
            $table->unsignedBigInteger('statusformat')->nullable();
            $table->integer('statuscetak')->Length(11)->nullable();
            $table->string('userbukacetak', 50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->Length(11)->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby', 50)->nullable();
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
        Schema::dropIfExists('pemutihansupirheader');
    }
}
