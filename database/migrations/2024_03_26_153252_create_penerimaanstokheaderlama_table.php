<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePenerimaanstokheaderlamaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('penerimaanstokheaderlama', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti', 50)->unique();
            $table->date('tglbukti')->nullable();
            $table->unsignedBigInteger('penerimaanstok_id')->nullable();
            $table->string('penerimaanstok_nobukti', 50)->nullable();
            $table->string('pengeluaranstok_nobukti', 50)->nullable();
            $table->string('nobuktisaldo', 50)->nullable();
            $table->date('tglbuktisaldo')->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->string('supplier',1000)->nullable();
            $table->string('nobon', 50)->nullable();
            $table->string('hutang_nobukti', 50)->nullable();
            $table->unsignedBigInteger('trado_id')->nullable();
            $table->string('trado',1000)->nullable();
            $table->unsignedBigInteger('gandengan_id')->nullable();
            $table->string('gandengan',1000)->nullable();
            $table->unsignedBigInteger('gudang_id')->nullable();
            $table->string('gudang',1000)->nullable();
            $table->integer('statuspindahgudang')->Length(11)->nullable();
            $table->unsignedBigInteger('gudangdari_id')->nullable();
            $table->string('gudangdari',1000)->nullable();
            $table->unsignedBigInteger('gudangke_id')->nullable();
            $table->string('gudangke',1000)->nullable();
            $table->unsignedBigInteger('tradodari_id')->nullable();
            $table->string('tradodari',1000)->nullable();
            $table->unsignedBigInteger('tradoke_id')->nullable();
            $table->string('tradoke',1000)->nullable();
            $table->unsignedBigInteger('gandengandari_id')->nullable();
            $table->string('gandengandari',1000)->nullable();
            $table->unsignedBigInteger('gandenganke_id')->nullable();
            $table->string('gandenganke',1000)->nullable();
            $table->string('coa', 50)->nullable();
            $table->longText('keterangan')->nullable();
            $table->unsignedBigInteger('statusformat')->nullable();
            $table->integer('statuscetak')->Length(11)->nullable();
            $table->string('userbukacetak', 50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->integer('jumlahcetak')->Length(11)->nullable();
            $table->integer('statusapprovaledit')->Length(11)->nullable();
            $table->string('userapprovaledit', 50)->nullable();
            $table->date('tglapprovaledit')->nullable();
            $table->dateTime('tglbatasedit')->nullable();
            $table->integer('statusapprovaleditketerangan')->Length(11)->nullable();
            $table->string('userapprovaleditketerangan', 50)->nullable();
            $table->date('tglapprovaleditketerangan')->nullable();
            $table->dateTime('tglbataseditketerangan')->nullable();
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
        Schema::dropIfExists('penerimaanstokheaderlama');
    }
}
