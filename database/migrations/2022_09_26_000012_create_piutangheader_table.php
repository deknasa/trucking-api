<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePiutangheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('piutangheader');

        Schema::create('piutangheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti', 50)->unique();
            $table->date('tglbukti')->nullable();
            $table->longText('keterangan')->nullable();
            $table->string('coadebet', 150)->nullable();
            $table->string('coakredit', 150)->nullable();
            $table->string('postingdari', 150)->nullable();
            $table->double('nominal', 15, 2)->nullable();
            $table->string('invoice_nobukti', 50)->nullable();
            $table->date('tgljatuhtempo')->nullable();
            $table->unsignedBigInteger('agen_id')->nullable();
            $table->unsignedBigInteger('statusformat')->nullable();
            $table->integer('jumlahcetak')->Length(11)->nullable();
            $table->integer('statuscetak')->Length(11)->nullable();
            $table->string('userbukacetak', 50)->nullable();
            $table->date('tglbukacetak')->nullable();
            $table->integer('statuskirimberkas')->Length(11)->nullable();
            $table->string('userkirimberkas',50)->nullable();
            $table->date('tglkirimberkas')->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby', 50)->nullable();
            $table->string('editing_by',50)->nullable();            
            $table->dateTime('editing_at')->nullable();            

            $table->timestamps();


            $table->foreign('agen_id', 'piutangheader_agen_agen_id_foreign')->references('id')->on('agen');
        });

        DB::statement("ALTER TABLE piutangheader NOCHECK CONSTRAINT piutangheader_agen_agen_id_foreign");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('piutangheader');
    }
}
