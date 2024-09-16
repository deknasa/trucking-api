<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


class CreatePengeluarandetailrincianjobTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('pengeluarandetailrincianjob');

        Schema::create('pengeluarandetailrincianjob', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pengeluaran_id')->nullable();
            $table->unsignedBigInteger('pengeluarandetail_id')->nullable();
            $table->string('nobukti',50)->nullable();
            $table->string('jobemkl_nobukti',50)->nullable();
            $table->double('nominal',15,2)->nullable();
            $table->longText('info')->nullable();
            $table->string('modifiedby',50)->nullable();
            $table->timestamps();

            $table->foreign('pengeluarandetail_id', 'pengeluarandetailrincianjob_pengeluarandetail_pengeluarandetail_id_foreign')->references('id')->on('pengeluarandetail')->onDelete('cascade');       
            $table->foreign('jobemkl_nobukti', 'pengeluarandetail_jobemkl_jobemkl_nobuktiforeign')->references('nobukti')->on('jobemkl');

        });

        DB::statement("ALTER TABLE pengeluarandetailrincianjob NOCHECK CONSTRAINT pengeluarandetail_jobemkl_jobemkl_nobuktiforeign");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pengeluarandetailrincianjob');
    }
}
