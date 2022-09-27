<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePelunasanpiutangheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('pelunasanpiutangheader');

        Schema::create('pelunasanpiutangheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->date('tglbukti')->default('1900/1/1');
            $table->longText('keterangan')->default('');
            $table->unsignedBigInteger('bank_id')->default('0');
            $table->unsignedBigInteger('agen_id')->default('0');
            $table->unsignedBigInteger('cabang_id')->default('0');
            $table->unsignedBigInteger('statusformat')->default(0);
            $table->string('modifiedby',50)->default('');
            $table->timestamps();

            $table->foreign('bank_id', 'pelunasanpiutangheader_bank_bank_id_foreign')->references('id')->on('bank');
            $table->foreign('agen_id', 'pelunasanpiutangheader_agen_agen_id_foreign')->references('id')->on('agen');
            $table->foreign('cabang_id', 'pelunasanpiutangheader_cabang_cabang_id_foreign')->references('id')->on('cabang');


        });

        DB::statement("ALTER TABLE pelunasanpiutangheader NOCHECK CONSTRAINT pelunasanpiutangheader_bank_bank_id_foreign");
        DB::statement("ALTER TABLE pelunasanpiutangheader NOCHECK CONSTRAINT pelunasanpiutangheader_agen_agen_id_foreign");
        DB::statement("ALTER TABLE pelunasanpiutangheader NOCHECK CONSTRAINT pelunasanpiutangheader_cabang_cabang_id_foreign");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pelunasanpiutangheader');
    }
}
