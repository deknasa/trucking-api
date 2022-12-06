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
            $table->string('nobukti',50)->unique();
            $table->date('tglbukti')->default('1900/1/1');
            $table->longText('keterangan')->default('');
            $table->string('postingdari',150)->default('');
            $table->double('nominal',15,2)->default('0');
            $table->string('invoice_nobukti',50)->default('');
            $table->unsignedBigInteger('agen_id')->default('0');
            $table->unsignedBigInteger('statusformat')->default(0);
            $table->integer('statuscetak')->Length(11)->default('0');
            $table->string('userbukacetak',50)->default('');
            $table->date('tglbukacetak')->default('1900/1/1');
            $table->string('modifiedby',50)->default('');
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
