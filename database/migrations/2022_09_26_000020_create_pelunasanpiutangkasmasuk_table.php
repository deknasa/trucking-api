<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


class CreatePelunasanpiutangkasmasukTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('pelunasanpiutangkasmasuk');

        Schema::create('pelunasanpiutangkasmasuk', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pelunasanpiutang_id')->default('0');
            $table->string('nobukti',50)->default('');
            $table->double('nominal',15,2)->default('');
            $table->integer('statuscair')->length(11)->default('0');
            $table->date('tglcair')->default('1900/1/1');
            $table->string('nowarkat',50)->default('');
            $table->string('bankwarkat',50)->default('');
            $table->longText('keterangan')->default('');
            $table->string('coadebet',50)->default('');
            $table->string('coakredit',50)->default('');
            $table->string('postingdari',50)->default('');
            $table->date('tgljt')->default('1900/1/1');
            $table->unsignedBigInteger('bank_id')->default('0');
            $table->unsignedBigInteger('bankpelanggan_id')->default('0');
            $table->string('modifiedby',50)->default('');
            $table->timestamps();


            $table->foreign('pelunasanpiutang_id', 'pelunasanpiutangkasmasuk_pelunasanpiutangheader_pelunasanpiutang_id_foreign')->references('id')->on('pelunasanpiutangheader')->onDelete('cascade');  
            $table->foreign('bank_id', 'pelunasanpiutangkasmasuk_bank_bank_id_foreign')->references('id')->on('bank');
            $table->foreign('bankpelanggan_id', 'pelunasanpiutangkasmasuk_bankpelanggan_bankpelanggan_id_foreign')->references('id')->on('bankpelanggan');
            $table->foreign('coadebet', 'pelunasanpiutangkasmasuk_akunpusat_coadebet_foreign')->references('coa')->on('akunpusat');
            $table->foreign('coakredit', 'pelunasanpiutangkasmasuk_akunpusat_coakredit_foreign')->references('coa')->on('akunpusat');

            
        });

        DB::statement("ALTER TABLE pelunasanpiutangkasmasuk NOCHECK CONSTRAINT pelunasanpiutangkasmasuk_bank_bank_id_foreign");
        DB::statement("ALTER TABLE pelunasanpiutangkasmasuk NOCHECK CONSTRAINT pelunasanpiutangkasmasuk_bankpelanggan_bankpelanggan_id_foreign");
        DB::statement("ALTER TABLE pelunasanpiutangkasmasuk NOCHECK CONSTRAINT pelunasanpiutangkasmasuk_akunpusat_coadebet_foreign");
        DB::statement("ALTER TABLE pelunasanpiutangkasmasuk NOCHECK CONSTRAINT pelunasanpiutangkasmasuk_akunpusat_coakredit_foreign");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pelunasanpiutangkasmasuk');
    }
}
