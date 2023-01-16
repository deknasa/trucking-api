<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePenerimaangiroheaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('penerimaangiroheader');

        Schema::create('penerimaangiroheader', function (Blueprint $table) {
            $table->id();
            $table->string('nobukti',50)->unique();
            $table->date('tglbukti')->default('1900/1/1');
            $table->unsignedBigInteger('pelanggan_id')->default('0');
            $table->string('postingdari',50)->default('');
            $table->string('diterimadari',100)->default('');
            $table->date('tgllunas')->default('1900/1/1');
            $table->unsignedBigInteger('cabang_id')->default('0');
            $table->integer('statusapproval')->length(11)->default('0');
            $table->string('userapproval',50)->default('');
            $table->date('tglapproval')->default('1900/1/1');
            $table->unsignedBigInteger('statusformat')->default(0);
            $table->integer('statuscetak')->Length(11)->default('0');
            $table->string('userbukacetak',50)->default('');
            $table->date('tglbukacetak')->default('1900/1/1');
            $table->integer('jumlahcetak')->Length(11)->default('0');
            $table->string('modifiedby',50)->default('');
            $table->timestamps();


            $table->foreign('pelanggan_id', 'penerimaangiroheader_pelanggan_pelanggan_id_foreign')->references('id')->on('pelanggan');    
            $table->foreign('cabang_id', 'penerimaangiroheader_cabang_cabang_id_foreign')->references('id')->on('cabang');    
        });

        DB::statement("ALTER TABLE penerimaangiroheader NOCHECK CONSTRAINT penerimaangiroheader_pelanggan_pelanggan_id_foreign");
        DB::statement("ALTER TABLE penerimaangiroheader NOCHECK CONSTRAINT penerimaangiroheader_cabang_cabang_id_foreign");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('penerimaangiroheader');
    }
}
